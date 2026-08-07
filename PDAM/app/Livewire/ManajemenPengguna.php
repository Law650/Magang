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
    public array $permissions = []; // Array of permission names

    // Modal state
    public bool $showModal = false;

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
        $this->showModal = true;
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
        // Load the primary role
        $this->role = $user->roles->first()?->name ?? $user->role; 
        // Load direct permissions
        $this->permissions = $user->permissions->pluck('name')->toArray();

        $this->password = '';
        $this->password_confirmation = '';

        $this->showModal = true;
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
            'role' => ['required', 'in:super_admin,admin,petugas'],
            'permissions' => ['array'],
        ];

        if (! $this->editingId) {
            // Creating — password required
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        } else {
            // Editing — password optional
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        }

        $validated = $this->validate($rules);

        // Security Check: Only super_admin can create/edit super_admins
        if ($validated['role'] === 'super_admin' && !auth()->user()->hasRole('super_admin')) {
            session()->flash('error', 'Hanya Super Admin yang dapat menugaskan role Super Admin.');
            return;
        }

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);

            // Security Check: Cannot change own role
            if ($user->id === auth()->id() && $user->roles->first()?->name !== $validated['role']) {
                session()->flash('error', 'Anda tidak dapat mengubah role akun Anda sendiri.');
                return;
            }

            // Security Check: Non-super_admins cannot edit super_admins
            if ($user->hasRole('super_admin') && !auth()->user()->hasRole('super_admin')) {
                session()->flash('error', 'Anda tidak memiliki wewenang untuk mengedit Super Admin.');
                return;
            }

            $data = [
                'name' => $validated['name'],
                'username' => $validated['username'],
                'phone' => $validated['phone'] ?? null,
                'role' => $validated['role'], // Keep for backward compat
            ];
            if (! empty($validated['password'])) {
                $data['password'] = $validated['password'];
            }
            $user->update($data);
            
            // Sync Spatie Roles and Permissions
            $user->syncRoles([$validated['role']]);
            $user->syncPermissions($validated['permissions'] ?? []);

        } else {
            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'password' => $validated['password'],
                'phone' => $validated['phone'] ?? null,
                'role' => $validated['role'], // Keep for backward compat
                'is_active' => true,
            ]);
            
            // Sync Spatie Roles and Permissions
            $user->syncRoles([$validated['role']]);
            $user->syncPermissions($validated['permissions'] ?? []);
        }

        $this->resetForm();
        $this->showModal = false;
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

        // Security Check: Non-super_admins cannot disable super_admins
        if ($user->hasRole('super_admin') && !auth()->user()->hasRole('super_admin')) {
            session()->flash('error', 'Anda tidak memiliki wewenang untuk menonaktifkan Super Admin.');
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

        // Security Check: Non-super_admins cannot delete super_admins
        if ($user->hasRole('super_admin') && !auth()->user()->hasRole('super_admin')) {
            session()->flash('error', 'Anda tidak memiliki wewenang untuk menghapus Super Admin.');
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
        $this->password_confirmation = '';
        $this->phone = '';
        $this->role = 'petugas';
        $this->permissions = [];
        $this->resetValidation();
    }

    public function render(): mixed
    {
        $query = User::with('roles', 'permissions');

        // Jika yang login adalah admin (bukan super_admin), hanya tampilkan petugas
        if (auth()->user()->hasRole('admin') && !auth()->user()->hasRole('super_admin')) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', 'petugas');
            });
        }

        if ($this->search !== '') {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('username', 'like', $searchTerm)
                    ->orWhere('phone', 'like', $searchTerm);
            });
        }

        $users = $query->orderBy('id', 'desc')->paginate(15);

        // Menghitung statistik (sesuaikan dengan visibility)
        $totalUsers = $users->total(); 
        $totalSuperAdmin = auth()->user()->hasRole('super_admin') ? User::role('super_admin')->count() : 0;
        $totalAdmin = auth()->user()->hasRole('super_admin') ? User::role('admin')->count() : 0;
        
        $petugasQuery = User::role('petugas');
        $totalPetugas = $petugasQuery->count();
        
        $aktifQuery = User::where('is_active', true);
        if (auth()->user()->hasRole('admin') && !auth()->user()->hasRole('super_admin')) {
            $aktifQuery->whereHas('roles', function ($q) {
                $q->where('name', 'petugas');
            });
        }
        $totalAktif = $aktifQuery->count();

        // Pass available permissions to the view for dynamic checkboxes
        $availablePermissions = \Spatie\Permission\Models\Permission::all();

        return view('livewire.manajemen-pengguna', [
            'users' => $users,
            'totalUsers' => $totalUsers,
            'totalSuperAdmin' => $totalSuperAdmin,
            'totalAdmin' => $totalAdmin,
            'totalPetugas' => $totalPetugas,
            'totalAktif' => $totalAktif,
            'availablePermissions' => $availablePermissions,
        ])->layout('components.layouts.app', ['title' => 'Manajemen Pengguna']);
    }
}
