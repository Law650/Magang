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
            // ── Admin ──────────────────────────────────────────
            [
                'name' => 'Administrator PDAM',
                'username' => 'admin',
                'email' => 'admin@pdam.go.id',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
                'phone' => '081200000001',
                'is_active' => true,
            ],

            // ── Petugas ────────────────────────────────────────
            [
                'name' => 'B',
                'username' => 'b',
                'email' => 'b.petugas@pdam.go.id',
                'password' => 'password',
                'role' => User::ROLE_PETUGAS,
                'phone' => '081200000004',
                'is_active' => true,
            ],
            [
                'name' => 'A',
                'username' => 'a',
                'email' => 'a.petugas@pdam.go.id',
                'password' => 'password',
                'role' => User::ROLE_PETUGAS,
                'phone' => '081200000005',
                'is_active' => true,
            ],
            [
                'name' => 'C',
                'username' => 'c',
                'email' => 'c.petugas@pdam.go.id',
                'password' => 'password',
                'role' => User::ROLE_PETUGAS,
                'phone' => '081200000006',
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData,
            );
        }
    }
}
