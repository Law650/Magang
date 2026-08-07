<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder akun pengguna PDAM Monitor.
 */
class UserSeeder extends Seeder
{
    /**
     * Seed akun demo dengan 2 role: Admin & Pekerja.
     */
    public function run(): void
    {
        $users = [
            // ── Super Admin ──────────────────────────────────────────
            [
                'name' => 'Super Administrator',
                'username' => 'superadmin',
                'password' => 'password',
                'role' => User::ROLE_ADMIN, // Will be migrated to super_admin by RolePermissionSeeder if run after
                'phone' => '081200000000',
                'is_active' => true,
            ],
            // ── Admin ──────────────────────────────────────────
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
                'phone' => '081200000001',
                'is_active' => true,
            ],

            // ── Petugas ────────────────────────────────────────
            [
                'name' => 'Petugas Lapangan',
                'username' => 'petugas',
                'password' => 'password',
                'role' => User::ROLE_PETUGAS,
                'phone' => '081200000002',
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['username' => $userData['username']],
                $userData,
            );
        }
    }
}
