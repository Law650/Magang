<?php

namespace App\Livewire;

use App\Models\AsetValve;
use App\Models\Lokasi;
use Livewire\Component;
use Livewire\WithPagination;

class ManajemenAset extends Component
{
    use WithPagination;

    public string $search = '';

    // Form fields
    public ?int $editingId = null;
    public string $nama_aset = '';
    public string $nama_lokasi = '';
    public string $kapasitas_full_putaran = '';
    public string $kapasitas_full_pecahan = '0';
    public string $latitude = '';
    public string $longitude = '';
    public string $kondisi_awal = 'buka_full';
    public string $custom_tutupan_bulat = '';
    public string $custom_tutupan_pecahan = '0';

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
            $this->selectedRows = AsetValve::with('lokasi')
                ->where(function ($q) {
                    $searchTerm = '%' . $this->search . '%';
                    $q->where('nama_aset', 'like', $searchTerm)
                      ->orWhereHas('lokasi', fn ($q) => $q->where('nama_lokasi', 'like', $searchTerm));
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
            AsetValve::whereIn('id', $this->selectedRows)->delete();
            $this->resetSelection();
            session()->flash('success', 'Aset terpilih berhasil dihapus.');
        }
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
        $aset = AsetValve::with('lokasi')->findOrFail($id);

        $this->editingId = $aset->id;
        $this->nama_aset = $aset->nama_aset;
        $this->nama_lokasi = $aset->lokasi->nama_lokasi;
        
        // Pisahkan kapasitas full ke bulat + pecahan
        $kapFull = (float) $aset->kapasitas_full_putaran;
        $kapBulat = (int) floor($kapFull);
        $kapPecahan = round($kapFull - $kapBulat, 3);
        $this->kapasitas_full_putaran = (string) $kapBulat;
        $this->kapasitas_full_pecahan = (string) $kapPecahan;
        
        $this->latitude = (string) ($aset->latitude ?? '');
        $this->longitude = (string) ($aset->longitude ?? '');

        $this->dispatch('open-modal');
    }

    /**
     * Save (create or update) an asset.
     */
    public function save(): void
    {
        $validated = $this->validate([
            'nama_aset' => ['required', 'string', 'max:150'],
            'nama_lokasi' => ['required', 'string', 'max:150'],
            'kapasitas_full_putaran' => ['required', 'integer', 'min:0'],
            'kapasitas_full_pecahan' => ['nullable', 'in:0,0.125,0.25,0.375,0.5,0.625,0.75,0.875'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'kondisi_awal' => ['nullable', 'in:buka_full,tutup_full,custom'],
            'custom_tutupan_bulat' => ['nullable', 'integer', 'min:0'],
            'custom_tutupan_pecahan' => ['nullable', 'in:0,0.125,0.25,0.375,0.5,0.625,0.75,0.875'],
        ]);

        // Gabungkan kapasitas full: bulat + pecahan
        $kapasitasFull = (float) $validated['kapasitas_full_putaran'] + (float) ($this->kapasitas_full_pecahan);

        $latVal = $validated['latitude'] !== '' && $validated['latitude'] !== null ? $validated['latitude'] : null;
        $lngVal = $validated['longitude'] !== '' && $validated['longitude'] !== null ? $validated['longitude'] : null;

        $lokasi = Lokasi::firstOrCreate(
            ['nama_lokasi' => $validated['nama_lokasi'], 'jenis' => 'valve'],
            ['latitude' => $latVal, 'longitude' => $lngVal]
        );

        // Update coordinates if they were provided
        if ($latVal !== null || $lngVal !== null) {
            $lokasi->update(['latitude' => $latVal, 'longitude' => $lngVal]);
        }

        if ($this->editingId) {
            $aset = AsetValve::findOrFail($this->editingId);
            $aset->update([
                'lokasi_id' => $lokasi->id,
                'nama_aset' => $validated['nama_aset'],
                'kapasitas_full_putaran' => $kapasitasFull,
                'latitude' => $latVal,
                'longitude' => $lngVal,
            ]);
        } else {
            $totalTutupan = 0;
            if ($this->kondisi_awal === 'tutup_full') {
                $totalTutupan = $kapasitasFull;
            } elseif ($this->kondisi_awal === 'custom' && $this->custom_tutupan_bulat !== '') {
                $totalTutupan = (float) $this->custom_tutupan_bulat + (float) $this->custom_tutupan_pecahan;
                // clamp totalTutupan so it doesn't exceed kapasitas
                if ($totalTutupan > $kapasitasFull) {
                    $totalTutupan = $kapasitasFull;
                }
            }

            AsetValve::create([
                'lokasi_id' => $lokasi->id,
                'nama_aset' => $validated['nama_aset'],
                'kapasitas_full_putaran' => $kapasitasFull,
                'total_tutupan_saat_ini' => $totalTutupan,
                'latitude' => $latVal,
                'longitude' => $lngVal,
            ]);
        }

        $this->resetForm();
        $this->dispatch('close-modal');
    }

    /**
     * Delete an asset (soft delete).
     */
    public function delete(int $id): void
    {
        AsetValve::findOrFail($id)->delete();
        $this->resetSelection();
        session()->flash('success', 'Aset berhasil dihapus.');
    }

    /**
     * Reset form fields.
     */
    private function resetForm(): void
    {
        $this->editingId = null;
        $this->nama_aset = '';
        $this->nama_lokasi = '';
        $this->kapasitas_full_putaran = '';
        $this->kapasitas_full_pecahan = '0';
        $this->latitude = '';
        $this->longitude = '';
        $this->kondisi_awal = 'buka_full';
        $this->custom_tutupan_bulat = '';
        $this->custom_tutupan_pecahan = '0';
        $this->resetValidation();
    }

    /**
     * Get all unique lokasi names for autocomplete.
     *
     * @return array<int, string>
     */
    public function getLokasiListProperty(): array
    {
        return Lokasi::where('jenis', 'valve')->orderBy('nama_lokasi')->pluck('nama_lokasi')->toArray();
    }

    public function render(): mixed
    {
        $query = AsetValve::with('lokasi');

        if ($this->search !== '') {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_aset', 'like', $searchTerm)
                    ->orWhereHas('lokasi', fn ($q) => $q->where('nama_lokasi', 'like', $searchTerm));
            });
        }

        $asets = $query->orderBy('id', 'desc')->paginate(15);

        // Stats
        $totalAset = AsetValve::count();
        $totalLokasi = Lokasi::where('jenis', 'valve')->count();
        $rataPersentase = AsetValve::count() > 0
            ? round(AsetValve::all()->avg(fn ($a) => $a->persentase_bukaan), 1)
            : 0;
        $asetKritis = AsetValve::all()->filter(fn ($a) => $a->persentase_bukaan < 25)->count();

        return view('livewire.manajemen-aset', [
            'asets' => $asets,
            'totalAset' => $totalAset,
            'totalLokasi' => $totalLokasi,
            'rataPersentase' => $rataPersentase,
            'asetKritis' => $asetKritis,
        ])->layout('components.layouts.app', ['title' => 'Manajemen Aset']);
    }
}
