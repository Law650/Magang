<?php

namespace App\Livewire;

use App\Models\LogTekanan;
use Livewire\Component;
use Livewire\WithPagination;

class LogTekananMonitor extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $startDate = '';
    public string $endDate = '';

    public array $selectedRows = [];
    public bool $selectAll = false;

    /**
     * Reset pagination when filters change.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedStartDate(): void
    {
        $this->resetPage();
    }

    public function updatedEndDate(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Set the active filter.
     */
    public function setFilter(string $status): void
    {
        $this->filterStatus = $status;
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedRows = $this->buildQuery()->orderBy('waktu_pengecekan', 'desc')->paginate(15)->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    private function resetSelection(): void
    {
        $this->selectedRows = [];
        $this->selectAll = false;
    }

    public function deleteSelected(): void
    {
        if (!empty($this->selectedRows)) {
            LogTekanan::whereIn('id', $this->selectedRows)->delete();
            $this->resetSelection();
            $this->dispatch('chart-data-updated', chartData: $this->chartData);
        }
    }

    public function deleteSingle($id): void
    {
        LogTekanan::find($id)?->delete();
        $this->resetSelection();
        $this->dispatch('chart-data-updated', chartData: $this->chartData);
    }

    /**
     * Build the filtered query.
     *
     * @return \Illuminate\Database\Eloquent\Builder<LogTekanan>
     */
    private function buildQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = LogTekanan::with('lokasi');

        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->search !== '') {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('lokasi', fn ($q) => $q->where('nama_lokasi', 'like', $searchTerm))
                  ->orWhere('no_sr', 'like', $searchTerm);
            });
        }

        if ($this->startDate !== '') {
            $query->whereDate('waktu_pengecekan', '>=', $this->startDate);
        }

        if ($this->endDate !== '') {
            $query->whereDate('waktu_pengecekan', '<=', $this->endDate);
        }

        return $query;
    }

    /**
     * Get chart data: latest pressure per location.
     *
     * @return array<string, mixed>
     */
    public function getChartDataProperty(): array
    {
        $latestPerLokasi = LogTekanan::with('lokasi')
            ->selectRaw('lokasi_id, MAX(id) as latest_id')
            ->groupBy('lokasi_id')
            ->pluck('latest_id');

        $query = LogTekanan::with('lokasi')
            ->whereIn('id', $latestPerLokasi);

        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }

        $latestLogs = $query->orderBy('lokasi_id')->get();

        $labels = [];
        $values = [];
        $colors = [];

        foreach ($latestLogs as $log) {
            $labels[] = $log->no_sr ?: ($log->lokasi->nama_lokasi ?? 'Unknown');
            $values[] = (float) $log->nilai_tekanan;
            $colors[] = match ($log->status) {
                'normal' => 'rgba(34, 197, 94, 0.8)',
                'rendah' => 'rgba(234, 179, 8, 0.8)',
                'kritis' => 'rgba(239, 68, 68, 0.8)',
                default => 'rgba(148, 163, 184, 0.8)',
            };
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'colors' => $colors,
        ];
    }

    public function render(): mixed
    {
        $query = $this->buildQuery();
        $logs = $query->orderBy('waktu_pengecekan', 'desc')->paginate(15);

        // Stats
        $statsQuery = $this->buildQuery();
        $totalPengecekan = $statsQuery->count();
        $rataTekanan = round((clone $statsQuery)->avg('nilai_tekanan') ?? 0, 2);
        $kondisiNormal = (clone $statsQuery)->where('status', 'normal')->count();
        $peringatanKritis = (clone $statsQuery)->where('status', 'kritis')->count();

        $this->dispatch('chart-data-updated', chartData: $this->chartData);

        return view('livewire.log-tekanan-monitor', [
            'logs' => $logs,
            'totalPengecekan' => $totalPengecekan,
            'rataTekanan' => $rataTekanan,
            'kondisiNormal' => $kondisiNormal,
            'peringatanKritis' => $peringatanKritis,
            'chartData' => $this->chartData,
        ])->layout('components.layouts.app', ['title' => 'Monitoring Tekanan Air']);
    }
}
