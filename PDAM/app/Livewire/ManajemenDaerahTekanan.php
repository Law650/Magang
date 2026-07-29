<?php

namespace App\Livewire;

use App\Models\Lokasi;
use Livewire\Component;
use Livewire\WithPagination;

class ManajemenDaerahTekanan extends Component
{
    use WithPagination;

    public string $search = '';

    // Form fields
    public ?int $editingId = null;
    public string $nama_lokasi = '';
    public ?string $latitude = null;
    public ?string $longitude = null;

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
        $lokasi = Lokasi::findOrFail($id);

        $this->editingId = $lokasi->id;
        $this->nama_lokasi = $lokasi->nama_lokasi;
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

        $uniqueRule = $this->editingId
            ? 'unique:lokasis,nama_lokasi,' . $this->editingId
            : 'unique:lokasis,nama_lokasi';

        $validated = $this->validate([
            'nama_lokasi' => ['required', 'string', 'max:150', $uniqueRule],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ], [
            'nama_lokasi.unique' => 'Nama daerah tekanan ini sudah ada di sistem.',
        ]);

        if ($this->editingId) {
            $lokasi = Lokasi::findOrFail($this->editingId);
            $lokasi->update([
                'nama_lokasi' => $validated['nama_lokasi'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);
        } else {
            Lokasi::create([
                'jenis' => 'tekanan',
                'nama_lokasi' => $validated['nama_lokasi'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);
        }

        $this->resetForm();
        $this->dispatch('close-modal');
    }

    /**
     * Delete a region.
     */
    public function delete(int $id): void
    {
        Lokasi::findOrFail($id)->delete();
    }

    /**
     * Reset form fields.
     */
    private function resetForm(): void
    {
        $this->editingId = null;
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
            $query->where('nama_lokasi', 'like', $searchTerm);
        }

        $lokasis = $query->orderBy('id', 'desc')->paginate(15);

        return view('livewire.manajemen-daerah-tekanan', [
            'lokasis' => $lokasis,
            'totalLokasi' => Lokasi::where('jenis', 'tekanan')->count(),
        ])->layout('components.layouts.app', ['title' => 'Manajemen Daerah Tekanan']);
    }
}
