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
        return LogValve::with('asetValve')
            ->orderBy('waktu_kegiatan', 'desc')
            ->limit(10)
            ->get();
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
