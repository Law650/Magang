<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder akun pengguna demo PDAM Monitor.
 *
 * Akun-akun ini dirancang agar bisa digunakan untuk:
 * - Login Web Dashboard (Fase 1)
 * - Login Mobile App Teknisi (Fase 2) — kredensial yang sama
 *
 * Password default: "password" (di-hash Bcrypt otomatis via cast 'hashed')
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
                'email' => 'admin@pdam.go.id',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
                'phone' => '081200000001',
                'is_active' => true,
            ],

            // ── Manajemen ──────────────────────────────────────
            [
                'name' => 'Kepala Bagian Distribusi',
                'email' => 'manajemen@pdam.go.id',
                'password' => 'password',
                'role' => User::ROLE_MANAJEMEN,
                'phone' => '081200000002',
                'is_active' => true,
            ],
            [
                'name' => 'Kepala Bagian Teknik',
                'email' => 'teknik@pdam.go.id',
                'password' => 'password',
                'role' => User::ROLE_MANAJEMEN,
                'phone' => '081200000003',
                'is_active' => true,
            ],

            // ── Teknisi ────────────────────────────────────────
            [
                'name' => 'B',
                'email' => 'b.teknisi@pdam.go.id',
                'password' => 'password',
                'role' => User::ROLE_TEKNISI,
                'phone' => '081200000004',
                'is_active' => true,
            ],
            [
                'name' => 'A',
                'email' => 'a.teknisi@pdam.go.id',
                'password' => 'password',
                'role' => User::ROLE_TEKNISI,
                'phone' => '081200000005',
                'is_active' => true,
            ],
            [
                'name' => 'C',
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
