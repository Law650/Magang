<?php

namespace App\Livewire;

use App\Models\LogValve;
use Livewire\Component;
use Livewire\WithPagination;

class LogValveRiwayat extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterAksi = '';
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

    public function updatedFilterAksi(): void
    {
        $this->resetPage();
    }

    /**
     * Set the active filter.
     */
    public function setFilter(string $aksi): void
    {
        $this->filterAksi = $aksi;
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedRows = $this->buildQuery()->orderBy('waktu_kegiatan', 'desc')->paginate(15)->pluck('id')->map(fn($id) => (string) $id)->toArray();
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
            LogValve::whereIn('id', $this->selectedRows)->delete();
            $this->resetSelection();
        }
    }

    public function deleteSingle($id): void
    {
        LogValve::find($id)?->delete();
        $this->resetSelection();
    }

    /**
     * Build the filtered query (shared between render and stats).
     *
     * @return \Illuminate\Database\Eloquent\Builder<LogValve>
     */
    private function buildQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = LogValve::with(['asetValve.lokasi']);

        if ($this->filterAksi !== '') {
            if ($this->filterAksi === 'cek') {
                $query->where('aksi_kerja', 'buka')->where('jumlah_putaran', 0);
            } elseif ($this->filterAksi === 'buka') {
                $query->where('aksi_kerja', 'buka')->where('jumlah_putaran', '>', 0);
            } else {
                $query->where('aksi_kerja', $this->filterAksi);
            }
        }

        if ($this->search !== '') {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_teknisi', 'like', $searchTerm)
                    ->orWhereHas('asetValve', fn ($q) => $q->where('nama_aset', 'like', $searchTerm))
                    ->orWhereHas('asetValve.lokasi', fn ($q) => $q->where('nama_lokasi', 'like', $searchTerm));
            });
        }

        if ($this->startDate !== '') {
            $query->whereDate('waktu_kegiatan', '>=', $this->startDate);
        }

        if ($this->endDate !== '') {
            $query->whereDate('waktu_kegiatan', '<=', $this->endDate);
        }

        return $query;
    }

    public function render(): mixed
    {
        $query = $this->buildQuery();

        $logs = $query->orderBy('waktu_kegiatan', 'desc')->paginate(15);

        // Stats based on filtered data
        $statsQuery = $this->buildQuery();
        $totalLog = $statsQuery->count();
        $jumlahBuka = (clone $statsQuery)->where('aksi_kerja', 'buka')->where('jumlah_putaran', '>', 0)->count();
        $jumlahTutup = (clone $statsQuery)->where('aksi_kerja', 'tutup')->count();
        $jumlahCek = (clone $statsQuery)->where('aksi_kerja', 'buka')->where('jumlah_putaran', 0)->count();
        $rataPutaran = round((clone $statsQuery)->avg('jumlah_putaran') ?? 0, 2);

        return view('livewire.log-valve-riwayat', [
            'logs' => $logs,
            'totalLog' => $totalLog,
            'jumlahBuka' => $jumlahBuka,
            'jumlahTutup' => $jumlahTutup,
            'jumlahCek' => $jumlahCek,
            'rataPutaran' => $rataPutaran,
        ])->layout('components.layouts.app', ['title' => 'Log Aktivitas Valve']);
    }
}
