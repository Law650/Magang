<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ManajemenPengguna extends Component
{
    use WithPagination;

    public string $search = '';

    // Form fields
    public ?int $editingId = null;
    public string $name = '';
    public string $username = '';

    public string $password = '';
    public string $password_confirmation = '';
    public string $phone = '';
    public string $role = 'petugas';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Open create modal.
     */
    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('open-user-modal');
    }

    /**
     * Fill form for editing.
     */
    public function edit(int $id): void
    {
        $user = User::findOrFail($id);

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->username = $user->username ?? '';

        $this->phone = $user->phone ?? '';
        $this->role = $user->role;
        $this->password = '';
        $this->password_confirmation = '';

        $this->dispatch('open-user-modal');
    }

    /**
     * Save (create or update) a user.
     */
    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($this->editingId)],

            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'in:admin,petugas'],
        ];

        if (! $this->editingId) {
            // Creating — password required
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        } else {
            // Editing — password optional
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        }

        $validated = $this->validate($rules);

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $data = [
                'name' => $validated['name'],
                'username' => $validated['username'],

                'phone' => $validated['phone'] ?? null,
                'role' => $validated['role'],
            ];
            if (! empty($validated['password'])) {
                $data['password'] = $validated['password'];
            }
            $user->update($data);
        } else {
            User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],

                'password' => $validated['password'],
                'phone' => $validated['phone'] ?? null,
                'role' => $validated['role'],
                'is_active' => true,
            ]);
        }

        $this->resetForm();
        $this->dispatch('close-user-modal');
    }

    /**
     * Toggle user active status (soft-disable).
     */
    public function toggleActive(int $id): void
    {
        $user = User::findOrFail($id);

        // Jangan bisa menonaktifkan diri sendiri
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Tidak bisa menonaktifkan akun sendiri.');
            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
    }

    /**
     * Hapus akun pengguna.
     */
    public function delete(int $id): void
    {
        $user = User::findOrFail($id);

        // Jangan bisa menghapus akun sendiri
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Tidak bisa menghapus akun sendiri.');
            return;
        }

        $user->delete();
        session()->flash('message', 'Akun berhasil dihapus.');
    }

    /**
     * Reset form fields.
     */
    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->username = '';
        $this->password = '';
        $this->phone = '';
        $this->role = 'petugas';
        $this->is_active = true;
    }

    public function render(): mixed
    {
        $query = User::query();

        if ($this->search !== '') {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('username', 'like', $searchTerm)
                    ->orWhere('phone', 'like', $searchTerm);
            });
        }

        $users = $query->orderBy('id', 'desc')->paginate(15);

        $totalUsers = User::count();
        $totalAdmin = User::where('role', 'admin')->count();
        $totalPetugas = User::where('role', 'petugas')->count();
        $totalAktif = User::where('is_active', true)->count();

        return view('livewire.manajemen-pengguna', [
            'users' => $users,
            'totalUsers' => $totalUsers,
            'totalAdmin' => $totalAdmin,
            'totalPetugas' => $totalPetugas,
            'totalAktif' => $totalAktif,
        ])->layout('components.layouts.app', ['title' => 'Manajemen Pengguna']);
    }
}
