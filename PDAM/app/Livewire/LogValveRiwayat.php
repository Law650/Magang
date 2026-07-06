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

    /**
     * Reset pagination when filters change.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
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
            $query->where('aksi_kerja', $this->filterAksi);
        }

        if ($this->search !== '') {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_teknisi', 'like', $searchTerm)
                    ->orWhereHas('asetValve', fn ($q) => $q->where('nama_aset', 'like', $searchTerm))
                    ->orWhereHas('asetValve.lokasi', fn ($q) => $q->where('nama_lokasi', 'like', $searchTerm));
            });
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
        $jumlahBuka = (clone $statsQuery)->where('aksi_kerja', 'buka')->count();
        $jumlahTutup = (clone $statsQuery)->where('aksi_kerja', 'tutup')->count();
        $rataPutaran = round((clone $statsQuery)->avg('jumlah_putaran') ?? 0, 2);

        return view('livewire.log-valve-riwayat', [
            'logs' => $logs,
            'totalLog' => $totalLog,
            'jumlahBuka' => $jumlahBuka,
            'jumlahTutup' => $jumlahTutup,
            'rataPutaran' => $rataPutaran,
        ])->layout('components.layouts.app', ['title' => 'Log Aktivitas Valve']);
    }
}
