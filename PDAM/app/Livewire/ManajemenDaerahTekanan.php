<?php

namespace App\Livewire;

use App\Models\Lokasi;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Imports\LokasiImport;
use Maatwebsite\Excel\Facades\Excel;

class ManajemenDaerahTekanan extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';

    // Form fields
    public ?int $editingId = null;
    public string $no_sr = '';
    public string $nama_pelanggan = '';
    public string $alamat = '';
    public string $desa = '';
    public string $nama_lokasi = ''; // Tetap dipertahankan sebagai desa atau identifier area
    public ?string $latitude = null;
    public ?string $longitude = null;

    // Excel import
    public $excelFile;

    // Selected rows for bulk actions
    public array $selectedRows = [];
    public bool $selectAll = false;

    /**
     * Reset pagination when search changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedRows = Lokasi::where('jenis', 'tekanan')
                ->where(function($q) {
                    $searchTerm = '%' . $this->search . '%';
                    $q->where('no_sr', 'like', $searchTerm)
                      ->orWhere('nama_pelanggan', 'like', $searchTerm)
                      ->orWhere('desa', 'like', $searchTerm)
                      ->orWhere('nama_lokasi', 'like', $searchTerm);
                })
                ->orderBy('id', 'desc')->paginate(15)->pluck('id')->map(fn($id) => (string) $id)->toArray();
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
            Lokasi::whereIn('id', $this->selectedRows)->delete();
            $this->resetSelection();
            session()->flash('success', 'Daerah terpilih berhasil dihapus.');
        }
    }

    /**
     * Delete a single region.
     */
    public function deleteSingle(int $id): void
    {
        Lokasi::findOrFail($id)->delete();
        $this->resetSelection();
        session()->flash('success', 'Daerah berhasil dihapus.');
    }

    /**
     * Open create modal (reset form).
     */
    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('open-modal');
    }

    /**
     * Fill form for editing.
     */
    public function edit(int $id): void
    {
        $lokasi = Lokasi::findOrFail($id);

        $this->editingId = $lokasi->id;
        $this->no_sr = $lokasi->no_sr ?? '';
        $this->nama_pelanggan = $lokasi->nama_pelanggan ?? '';
        $this->alamat = $lokasi->alamat ?? '';
        $this->desa = $lokasi->desa ?? '';
        $this->nama_lokasi = $lokasi->nama_lokasi ?? '';
        $this->latitude = $lokasi->latitude !== null ? (string) $lokasi->latitude : null;
        $this->longitude = $lokasi->longitude !== null ? (string) $lokasi->longitude : null;

        $this->dispatch('open-modal');
    }

    /**
     * Save (create or update) a region.
     */
    public function save(): void
    {
        if ($this->latitude === '') $this->latitude = null;
        if ($this->longitude === '') $this->longitude = null;

        $uniqueSrRule = $this->editingId
            ? 'unique:lokasis,no_sr,' . $this->editingId
            : 'unique:lokasis,no_sr';

        $validated = $this->validate([
            'no_sr' => ['required', 'string', 'max:50', $uniqueSrRule],
            'nama_pelanggan' => ['required', 'string', 'max:150'],
            'alamat' => ['nullable', 'string'],
            'desa' => ['required', 'string', 'max:100'],
            'nama_lokasi' => ['nullable', 'string', 'max:150'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ], [
            'no_sr.unique' => 'Nomor SR ini sudah ada di sistem.',
        ]);

        // Jika nama_lokasi kosong, isi dengan format yang pasti unik (No SR - Nama Pelanggan)
        $namaLokasiFinal = !empty($validated['nama_lokasi']) ? $validated['nama_lokasi'] : $validated['no_sr'] . ' - ' . $validated['nama_pelanggan'];

        if ($this->editingId) {
            $lokasi = Lokasi::findOrFail($this->editingId);
            $lokasi->update([
                'no_sr' => $validated['no_sr'],
                'nama_pelanggan' => $validated['nama_pelanggan'],
                'alamat' => $validated['alamat'],
                'desa' => $validated['desa'],
                'nama_lokasi' => $namaLokasiFinal,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);
        } else {
            Lokasi::create([
                'jenis' => 'tekanan',
                'no_sr' => $validated['no_sr'],
                'nama_pelanggan' => $validated['nama_pelanggan'],
                'alamat' => $validated['alamat'],
                'desa' => $validated['desa'],
                'nama_lokasi' => $namaLokasiFinal,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);
        }

        $this->resetForm();
        $this->dispatch('close-modal');
    }

    /**
     * Import from Excel.
     */
    public function importExcel(): void
    {
        $this->validate([
            'excelFile' => 'required|file|max:10240', // 10MB Max
        ]);

        try {
            $import = new LokasiImport;
            Excel::import($import, $this->excelFile->getRealPath());
            
            $this->reset('excelFile');
            $this->dispatch('close-import-modal');
            session()->flash('success', $import->importedCount . ' Data Pelanggan berhasil diimport.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengimport data: ' . $e->getMessage());
        }
    }

    /**
     * Reset form fields.
     */
    private function resetForm(): void
    {
        $this->editingId = null;
        $this->no_sr = '';
        $this->nama_pelanggan = '';
        $this->alamat = '';
        $this->desa = '';
        $this->nama_lokasi = '';
        $this->latitude = null;
        $this->longitude = null;
        $this->resetValidation();
    }

    public function render(): mixed
    {
        $query = Lokasi::where('jenis', 'tekanan');

        if ($this->search !== '') {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('no_sr', 'like', $searchTerm)
                  ->orWhere('nama_pelanggan', 'like', $searchTerm)
                  ->orWhere('desa', 'like', $searchTerm)
                  ->orWhere('nama_lokasi', 'like', $searchTerm);
            });
        }

        $lokasis = $query->orderBy('id', 'desc')->paginate(15);

        return view('livewire.manajemen-daerah-tekanan', [
            'lokasis' => $lokasis,
            'totalLokasi' => Lokasi::where('jenis', 'tekanan')->count(),
        ])->layout('components.layouts.app', ['title' => 'Manajemen Daerah Tekanan']);
    }
}
