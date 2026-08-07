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

use Spatie\Permission\Traits\HasRoles;

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
#[Fillable(['name', 'username', 'password', 'role', 'phone', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /** Role constants */
    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_PETUGAS = 'petugas';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Cek apakah user adalah Admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN) || $this->hasRole(self::ROLE_SUPER_ADMIN);
    }

    /**
     * Cek apakah user adalah Petugas.
     */
    public function isPetugas(): bool
    {
        return $this->hasRole(self::ROLE_PETUGAS);
    }

    /**
     * Get label role yang readable.
     */
    public function getRoleLabelAttribute(): string
    {
        if ($this->hasRole(self::ROLE_SUPER_ADMIN)) return 'Super Administrator';
        if ($this->hasRole(self::ROLE_ADMIN)) return 'Administrator';
        if ($this->hasRole(self::ROLE_PETUGAS)) return 'Petugas';
        
        return ucfirst($this->role); // Fallback to column
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
