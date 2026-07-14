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

    /**
     * Reset pagination when search changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
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
        $this->kapasitas_full_putaran = (string) $aset->kapasitas_full_putaran;

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
            'kapasitas_full_putaran' => ['required', 'numeric', 'min:0.01', 'regex:/^\d+(\.\d{1,2})?$/'],
        ]);

        $lokasi = Lokasi::firstOrCreate(
            ['nama_lokasi' => $validated['nama_lokasi'], 'jenis' => 'valve'],
            ['latitude' => null, 'longitude' => null]
        );

        if ($this->editingId) {
            $aset = AsetValve::findOrFail($this->editingId);
            $aset->update([
                'lokasi_id' => $lokasi->id,
                'nama_aset' => $validated['nama_aset'],
                'kapasitas_full_putaran' => $validated['kapasitas_full_putaran'],
            ]);
        } else {
            AsetValve::create([
                'lokasi_id' => $lokasi->id,
                'nama_aset' => $validated['nama_aset'],
                'kapasitas_full_putaran' => $validated['kapasitas_full_putaran'],
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
