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
     * Compute doughnut chart data for valve status distribution.
     * Bukaan Penuh: persentase_bukaan >= 90
     * Bukaan Sebagian: 10 < persentase_bukaan < 90
     * Tertutup Rapat: persentase_bukaan <= 10
     *
     * @return array{labels: string[], values: int[], colors: string[]}
     */
    private function getDoughnutChartData(): array
    {
        $allValves = AsetValve::all();

        $bukaanPenuh = $allValves->filter(fn ($v) => $v->persentase_bukaan >= 90)->count();
        $bukaanSebagian = $allValves->filter(fn ($v) => $v->persentase_bukaan > 10 && $v->persentase_bukaan < 90)->count();
        $tertutupRapat = $allValves->filter(fn ($v) => $v->persentase_bukaan <= 10)->count();

        return [
            'labels' => ['Bukaan Penuh', 'Bukaan Sebagian', 'Tertutup Rapat'],
            'values' => [$bukaanPenuh, $bukaanSebagian, $tertutupRapat],
            'colors' => ['#06b6d4', '#f59e0b', '#ef4444'],
        ];
    }

    /**
     * Compute line chart data for pressure trends over the last 7 days.
     *
     * @return array{labels: string[], values: float[]}
     */
    private function getLineChartData(): array
    {
        $labels = [];
        $values = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->translatedFormat('d M');

            $avg = LogTekanan::whereDate('waktu_pengecekan', $date)
                ->avg('nilai_tekanan');

            $values[] = round((float) ($avg ?? 0), 2);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
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
     * Get the 5 most recent valve log activities.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getRecentActivities()
    {
        return LogValve::with('asetValve')
            ->orderBy('waktu_kegiatan', 'desc')
            ->limit(5)
            ->get();
    }

    public function render(): mixed
    {
        return view('livewire.executive-dashboard', [
            'metrics' => $this->getMetrics(),
            'doughnutData' => $this->getDoughnutChartData(),
            'lineChartData' => $this->getLineChartData(),
            'valveSummary' => $this->getValveSummary(),
            'recentActivities' => $this->getRecentActivities(),
        ])->layout('components.layouts.app', ['title' => 'Executive Dashboard']);
    }
}
