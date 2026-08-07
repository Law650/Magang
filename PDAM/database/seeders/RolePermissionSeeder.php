<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create Permissions
        $permissions = [
            'view_dashboard',
            'view_peta_tekanan',
            'view_peta_valve',
            'manage_tekanan',
            'manage_valve',
            'manage_users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Create Roles & Assign Permissions

        // SUPER ADMIN: Gets everything
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all());

        // ADMIN: By default can view dashboard.
        // Specific permissions (manage_tekanan, manage_valve) will be assigned dynamically per-user.
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'view_dashboard'
        ]);

        // PETUGAS: Only views dashboard by default
        $petugas = Role::firstOrCreate(['name' => 'petugas']);
        $petugas->syncPermissions([
            'view_dashboard'
        ]);
        
        // Output info
        $this->command->info('Roles and Permissions seeded successfully!');

        // 3. Migrate Existing Users
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            if ($user->role === \App\Models\User::ROLE_ADMIN || $user->role === 'manajemen') {
                $user->assignRole('super_admin'); // Make existing admins into super_admin so they don't get locked out
            } elseif ($user->role === \App\Models\User::ROLE_PETUGAS || $user->role === 'teknisi') {
                $user->assignRole('petugas');
            }
        }
        $this->command->info('Existing users migrated to Spatie roles!');
    }
}
