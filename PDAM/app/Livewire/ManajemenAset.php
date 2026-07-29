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
    public string $latitude = '';
    public string $longitude = '';
    public string $kondisi_awal = 'buka_full';
    public string $custom_tutupan = '';

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
        $this->latitude = (string) ($aset->lokasi->latitude ?? '');
        $this->longitude = (string) ($aset->lokasi->longitude ?? '');

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
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'kondisi_awal' => ['nullable', 'in:buka_full,tutup_full,custom'],
            'custom_tutupan' => ['nullable', 'numeric', 'min:0'],
        ]);

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
                'kapasitas_full_putaran' => $validated['kapasitas_full_putaran'],
            ]);
        } else {
            $totalTutupan = 0;
            if ($this->kondisi_awal === 'tutup_full') {
                $totalTutupan = $validated['kapasitas_full_putaran'];
            } elseif ($this->kondisi_awal === 'custom' && $this->custom_tutupan !== '') {
                $totalTutupan = (float) $this->custom_tutupan;
                // clamp totalTutupan so it doesn't exceed kapasitas
                if ($totalTutupan > (float) $validated['kapasitas_full_putaran']) {
                    $totalTutupan = (float) $validated['kapasitas_full_putaran'];
                }
            }

            AsetValve::create([
                'lokasi_id' => $lokasi->id,
                'nama_aset' => $validated['nama_aset'],
                'kapasitas_full_putaran' => $validated['kapasitas_full_putaran'],
                'total_tutupan_saat_ini' => $totalTutupan,
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
        $this->latitude = '';
        $this->longitude = '';
        $this->kondisi_awal = 'buka_full';
        $this->custom_tutupan = '';
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
