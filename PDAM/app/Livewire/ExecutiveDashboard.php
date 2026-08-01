<?php

namespace App\Livewire;

use App\Models\AsetValve;
use App\Models\LogTekanan;
use App\Models\LogValve;
use App\Models\Lokasi;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ExecutiveDashboard extends Component
{
    /**
     * Compute key metrics for the dashboard cards.
     *
     * @return array{totalAset: int, totalAktivitasHariIni: int, daerahKritis: int, hasDaerahKritis: bool}
     */
    private function getMetrics(): array
    {
        $totalAset = AsetValve::count();

        $totalAktivitasHariIni = LogValve::whereDate('waktu_kegiatan', today())->count();

        // Daerah kritis: lokasi yang memiliki log tekanan terbaru dengan nilai 0 Bar
        $daerahKritis = LogTekanan::where('nilai_tekanan', 0)
            ->distinct('lokasi_id')
            ->count('lokasi_id');

        return [
            'totalAset' => $totalAset,
            'totalAktivitasHariIni' => $totalAktivitasHariIni,
            'daerahKritis' => $daerahKritis,
            'hasDaerahKritis' => $daerahKritis > 0,
        ];
    }

    /**
     * Compute doughnut chart data for distribution health status.
     * Based on latest pressure readings per daerah tekanan.
     *
     * @return array{labels: string[], values: int[], colors: string[]}
     */
    private function getDoughnutChartData(): array
    {
        $lokasis = Lokasi::where('jenis', 'tekanan')->get();

        $normal = 0;
        $rendah = 0;
        $kritis = 0;
        $belumAda = 0;

        foreach ($lokasis as $lokasi) {
            $latestLog = LogTekanan::where('lokasi_id', $lokasi->id)
                ->orderBy('waktu_pengecekan', 'desc')
                ->first();

            if (!$latestLog) {
                $belumAda++;
                continue;
            }

            match ($latestLog->status) {
                'normal' => $normal++,
                'rendah' => $rendah++,
                'kritis' => $kritis++,
                default => $belumAda++,
            };
        }

        $labels = ['Normal', 'Rendah', 'Kritis'];
        $values = [$normal, $rendah, $kritis];
        $colors = ['#10b981', '#f59e0b', '#ef4444'];

        if ($belumAda > 0) {
            $labels[] = 'Belum Ada Data';
            $values[] = $belumAda;
            $colors[] = '#64748b';
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'colors' => $colors,
        ];
    }

    /**
     * Get list of daerah with rendah or kritis pressure status.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getDaerahBermasalah()
    {
        $lokasis = Lokasi::where('jenis', 'tekanan')->get();

        return $lokasis->map(function ($lokasi) {
            $latestLog = LogTekanan::where('lokasi_id', $lokasi->id)
                ->orderBy('waktu_pengecekan', 'desc')
                ->first();

            if (!$latestLog || !in_array($latestLog->status, ['rendah', 'kritis'])) {
                return null;
            }

            return [
                'id' => $lokasi->id,
                'nama_lokasi' => $lokasi->nama_lokasi,
                'latitude' => $lokasi->latitude,
                'longitude' => $lokasi->longitude,
                'status' => $latestLog->status,
                'nilai_tekanan' => $latestLog->nilai_tekanan,
                'status_aliran' => $latestLog->status_aliran,
                'waktu' => $latestLog->waktu_pengecekan,
                'nama_teknisi' => $latestLog->nama_teknisi,
            ];
        })->filter()->sortBy(function ($item) {
            // Kritis di atas, lalu rendah
            return $item['status'] === 'kritis' ? 0 : 1;
        })->values();
    }

    /**
     * Get all valve assets with their opening status for the summary table.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getValveSummary()
    {
        return AsetValve::with('lokasi')
            ->orderBy('id')
            ->get()
            ->map(fn ($valve) => [
                'id' => $valve->id,
                'nama_aset' => $valve->nama_aset,
                'lokasi' => $valve->lokasi->nama_lokasi,
                'latitude' => $valve->latitude,
                'longitude' => $valve->longitude,
                'kapasitas_full' => (float) $valve->kapasitas_full_putaran,
                'total_tutupan' => (float) $valve->total_tutupan_saat_ini,
                'sisa_bukaan' => $valve->sisa_bukaan,
                'persentase_bukaan' => $valve->persentase_bukaan,
            ]);
    }

    /**
     * Get the 10 most recent valve log activities.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getRecentActivities()
    {
        $valveLogs = LogValve::with('asetValve.lokasi')
            ->orderBy('waktu_kegiatan', 'desc')
            ->limit(10)
            ->get()
            ->toBase()
            ->map(fn ($log) => [
                'type' => 'valve',
                'id' => $log->id,
                'nama_teknisi' => $log->nama_teknisi,
                'aksi_kerja' => $log->aksi_kerja,
                'jumlah_putaran' => $log->jumlah_putaran,
                'nama_aset' => $log->asetValve->nama_aset ?? '-',
                'lokasi' => $log->asetValve->lokasi->nama_lokasi ?? '-',
                'keterangan' => $log->keterangan,
                'waktu' => $log->waktu_kegiatan,
                'lat_master' => $log->asetValve->latitude ?? null,
                'lng_master' => $log->asetValve->longitude ?? null,
                'lat_input' => $log->latitude,
                'lng_input' => $log->longitude,
                // 'jarak_meter' => $log->jarak_dari_master,
                // 'status_radius' => $log->status_radius,
                'foto_eviden' => $log->foto_eviden ? \Illuminate\Support\Facades\Storage::url($log->foto_eviden) : null,
                'foto_eviden_2' => $log->foto_eviden_2 ? \Illuminate\Support\Facades\Storage::url($log->foto_eviden_2) : null,
            ]);

        $tekananLogs = LogTekanan::with('lokasi')
            ->orderBy('waktu_pengecekan', 'desc')
            ->limit(10)
            ->get()
            ->toBase()
            ->map(fn ($log) => [
                'type' => 'tekanan',
                'id' => $log->id,
                'nama_teknisi' => $log->nama_teknisi,
                'nilai_tekanan' => $log->nilai_tekanan,
                'status' => $log->status,
                'lokasi' => $log->lokasi->nama_lokasi ?? '-',
                'keterangan' => $log->keterangan,
                'waktu' => $log->waktu_pengecekan,
                'foto_eviden' => $log->foto_eviden ? \Illuminate\Support\Facades\Storage::url($log->foto_eviden) : null,
            ]);

        return $valveLogs->merge($tekananLogs)
            ->sortByDesc('waktu')
            ->take(10)
            ->values();
    }

    public function render(): mixed
    {
        return view('livewire.executive-dashboard', [
            'metrics' => $this->getMetrics(),
            'doughnutData' => $this->getDoughnutChartData(),
            'daerahBermasalah' => $this->getDaerahBermasalah(),
            'valveSummary' => $this->getValveSummary(),
            'recentActivities' => $this->getRecentActivities(),
        ])->layout('components.layouts.app', ['title' => 'Executive Dashboard']);
    }
}
