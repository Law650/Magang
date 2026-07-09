<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Model User — Autentikasi terpusat untuk Web & Mobile (Fase 2).
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string $role  admin|manajemen|teknisi
 * @property bool $is_active  Soft-disable tanpa hapus data
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'username', 'email', 'password', 'role', 'phone', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /** Role constants — sesuai ENUM di PRD §3.2 */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_MANAJEMEN = 'manajemen';
    public const ROLE_TEKNISI = 'teknisi';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Cek apakah user adalah Admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Cek apakah user adalah Manajemen.
     */
    public function isManajemen(): bool
    {
        return $this->role === self::ROLE_MANAJEMEN;
    }

    /**
     * Cek apakah user adalah Teknisi.
     */
    public function isTeknisi(): bool
    {
        return $this->role === self::ROLE_TEKNISI;
    }

    /**
     * Get label role yang readable.
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_MANAJEMEN => 'Manajemen',
            self::ROLE_TEKNISI => 'Teknisi',
            default => ucfirst($this->role),
        };
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }
}
