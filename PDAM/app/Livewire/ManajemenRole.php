<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ManajemenRole extends Component
{
    public array $rolePermissions = [];
    public $roles;
    public $permissions;

    public function mount()
    {
        // Load roles except super_admin (which is locked/always has all permissions)
        $this->roles = Role::where('name', '!=', 'super_admin')->get();
        $this->permissions = Permission::all();

        // Initialize state
        foreach ($this->roles as $role) {
            $this->rolePermissions[$role->name] = $role->permissions->pluck('name')->toArray();
        }
    }

    public function save()
    {
        // Security check: Must be super admin
        if (!auth()->user()->hasRole('super_admin')) {
            session()->flash('error', 'Anda tidak memiliki wewenang untuk mengelola fungsi Role.');
            return;
        }

        foreach ($this->roles as $role) {
            if (isset($this->rolePermissions[$role->name])) {
                $role->syncPermissions($this->rolePermissions[$role->name]);
            } else {
                $role->syncPermissions([]);
            }
        }

        session()->flash('message', 'Fungsi Role berhasil diperbarui!');
    }

    public function render()
    {
        return view('livewire.manajemen-role')->layout('components.layouts.app', ['title' => 'Manajemen Peran']);
    }
}
