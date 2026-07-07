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
     * Seed 6 akun demo dengan 3 role: Admin, Manajemen, Teknisi.
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

            // ── Manajemen ──────────────────────────────────────
            [
                'name' => 'Kepala Bagian Distribusi',
                'username' => 'kbd',
                'email' => 'manajemen@pdam.go.id',
                'password' => 'password',
                'role' => User::ROLE_MANAJEMEN,
                'phone' => '081200000002',
                'is_active' => true,
            ],

            // ── Teknisi ────────────────────────────────────────
            [
                'name' => 'B',
                'username' => 'b',
                'email' => 'b.teknisi@pdam.go.id',
                'password' => 'password',
                'role' => User::ROLE_TEKNISI,
                'phone' => '081200000004',
                'is_active' => true,
            ],
            [
                'name' => 'A',
                'username' => 'a',
                'email' => 'a.teknisi@pdam.go.id',
                'password' => 'password',
                'role' => User::ROLE_TEKNISI,
                'phone' => '081200000005',
                'is_active' => true,
            ],
            [
                'name' => 'C',
                'username' => 'c',
                'email' => 'C.teknisi@pdam.go.id',
                'password' => 'password',
                'role' => User::ROLE_TEKNISI,
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
