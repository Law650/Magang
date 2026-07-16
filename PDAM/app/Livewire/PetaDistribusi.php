<?php

namespace App\Livewire;

use App\Helpers\FormatHelper;
use App\Models\AsetValve;
use App\Models\Lokasi;
use App\Models\LogTekanan;
use Livewire\Component;

class PetaDistribusi extends Component
{
    public string $activeTab = 'gv';

    public array $gvMarkers = [];
    public array $tekananMarkers = [];
    public array $gvStats = [];
    public array $tekananStats = [];

    public function mount()
    {
        $this->refreshData();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function refreshData(): void
    {
        $this->gvMarkers = $this->getGvMarkers();
        $this->tekananMarkers = $this->getTekananMarkers();
        $this->gvStats = $this->getGvStats();
        $this->tekananStats = $this->getTekananStats();

        $this->dispatch('gv-markers-updated', markers: $this->gvMarkers);
        $this->dispatch('tekanan-markers-updated', markers: $this->tekananMarkers);
    }

    /**
     * Marker data dari master_aset_valve (Peta GV).
     */
    private function getGvMarkers(): array
    {
        $asets = AsetValve::with(['lokasi', 'lastLogValve.user'])
            ->whereHas('lokasi', fn ($q) => $q->whereNotNull('latitude')->whereNotNull('longitude'))
            ->get();

        return $asets->map(function ($aset) {
            $persentase = $aset->persentase_bukaan;
            $status = $persentase >= 75 ? 'penuh' : ($persentase >= 25 ? 'sebagian' : 'tertutup');

            $lastLog = $aset->lastLogValve;
            $teknisiTerakhir = $lastLog?->user?->name ?? '-';

            $lat = $aset->lokasi->latitude;
            $lng = $aset->lokasi->longitude;
            $foto = $lastLog?->foto_eviden ? asset('storage/' . $lastLog->foto_eviden) : null;

            return [
                'id' => $aset->id,
                'nama' => $aset->nama_aset,
                'lokasi' => $aset->lokasi->nama_lokasi ?? '-',
                'lat' => (float) $lat,
                'lng' => (float) $lng,
                'kapasitas' => (float) $aset->kapasitas_full_putaran,
                'sisaBukaan' => FormatHelper::putaran($aset->sisa_bukaan),
                'persentase' => $persentase,
                'status' => $status,
                'teknisiTerakhir' => $teknisiTerakhir,
                'foto' => $foto,
            ];
        })->toArray();
    }

    /**
     * Marker data dari lokasi + log_tekanan terbaru (Peta Tekanan).
     */
    private function getTekananMarkers(): array
    {
        $lokasis = Lokasi::where('jenis', 'tekanan')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return $lokasis->map(function ($lokasi) {
            $latestLog = LogTekanan::where('lokasi_id', $lokasi->id)
                ->orderBy('waktu_pengecekan', 'desc')
                ->first();

            $nilaiTekanan = $latestLog ? (float) $latestLog->nilai_tekanan : null;
            $status = $latestLog ? $latestLog->status : 'unknown';
            $statusAliran = $latestLog?->status_aliran;
            $waktu = $latestLog?->waktu_pengecekan;

            // Derive laju air dari tekanan jika status_aliran belum ada
            if ($nilaiTekanan !== null && $statusAliran === null) {
                $statusAliran = $nilaiTekanan > 0 ? 'mengalir' : 'tidak_mengalir';
            }

            $lat = $lokasi->latitude;
            $lng = $lokasi->longitude;
            $foto = $latestLog?->foto_eviden ? asset('storage/' . $latestLog->foto_eviden) : null;

            return [
                'id' => $lokasi->id,
                'nama' => $lokasi->nama_lokasi,
                'lat' => (float) $lat,
                'lng' => (float) $lng,
                'tekanan' => $nilaiTekanan,
                'status' => $status,
                'statusAliran' => $statusAliran,
                'waktu' => $waktu,
                'foto' => $foto,
            ];
        })->toArray();
    }

    /**
     * Stats for GV tab.
     */
    private function getGvStats(): array
    {
        $asets = AsetValve::all();
        $total = $asets->count();
        $penuh = 0;
        $sebagian = 0;
        $tertutup = 0;

        foreach ($asets as $aset) {
            $p = $aset->persentase_bukaan;
            if ($p >= 75) {
                $penuh++;
            } elseif ($p >= 25) {
                $sebagian++;
            } else {
                $tertutup++;
            }
        }

        return compact('total', 'penuh', 'sebagian', 'tertutup');
    }

    /**
     * Stats for Tekanan tab.
     */
    private function getTekananStats(): array
    {
        $lokasis = Lokasi::where('jenis', 'tekanan')->whereNotNull('latitude')->get();
        $total = $lokasis->count();
        $normal = 0;
        $rendah = 0;
        $kritis = 0;

        foreach ($lokasis as $lokasi) {
            $latest = LogTekanan::where('lokasi_id', $lokasi->id)
                ->orderBy('waktu_pengecekan', 'desc')
                ->first();
            if (! $latest) {
                continue;
            }
            match ($latest->status) {
                'normal' => $normal++,
                'rendah' => $rendah++,
                'kritis' => $kritis++,
                default => null,
            };
        }

        return compact('total', 'normal', 'rendah', 'kritis');
    }

    public function render(): mixed
    {
        $this->refreshData();
        return view('livewire.peta-distribusi')->layout('components.layouts.app', ['title' => 'Peta Distribusi']);
    }
}
