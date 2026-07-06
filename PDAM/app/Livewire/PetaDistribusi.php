<?php

namespace App\Livewire;

use App\Models\Lokasi;
use App\Models\LogTekanan;
use Livewire\Component;

class PetaDistribusi extends Component
{
    /**
     * Build map marker data for each lokasi.
     * Includes latest pressure reading and asset count.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getMarkers(): array
    {
        $lokasis = Lokasi::withCount('asetValves')
            ->with(['logTekanans' => function ($q) {
                $q->orderBy('waktu_pengecekan', 'desc');
            }, 'asetValves'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return $lokasis->map(function ($lokasi) {
            $latestTekanan = $lokasi->logTekanans->first();
            $nilaiTekanan = $latestTekanan ? (float) $latestTekanan->nilai_tekanan : null;
            $status = $latestTekanan ? $latestTekanan->status : 'unknown';

            $valves = $lokasi->asetValves->map(fn ($v) => [
                'nama' => $v->nama_aset,
                'kapasitas' => (float) $v->kapasitas_full_putaran,
                'tutupan' => (float) $v->total_tutupan_saat_ini,
                'sisaBukaan' => $v->sisa_bukaan,
                'persentase' => $v->persentase_bukaan,
            ])->values()->toArray();

            return [
                'id' => $lokasi->id,
                'nama' => $lokasi->nama_lokasi,
                'lat' => (float) $lokasi->latitude,
                'lng' => (float) $lokasi->longitude,
                'jumlahAset' => $lokasi->aset_valves_count,
                'tekanan' => $nilaiTekanan,
                'status' => $status,
                'valves' => $valves,
            ];
        })->toArray();
    }

    /**
     * Compute summary statistics for the sidebar.
     *
     * @return array{total: int, normal: int, rendah: int, kritis: int}
     */
    private function getStats(): array
    {
        $lokasis = Lokasi::whereNotNull('latitude')->get();
        $total = $lokasis->count();
        $normal = 0;
        $rendah = 0;
        $kritis = 0;

        foreach ($lokasis as $lokasi) {
            $latest = $lokasi->logTekanans()->orderBy('waktu_pengecekan', 'desc')->first();
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
        return view('livewire.peta-distribusi', [
            'markers' => $this->getMarkers(),
            'stats' => $this->getStats(),
        ])->layout('components.layouts.app', ['title' => 'Peta Distribusi Aset']);
    }
}
