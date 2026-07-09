<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Livewire Login Component — Autentikasi Web PDAM Monitor.
 *
 * Implementasi FR-001 s/d FR-005 (PRD §4.1.1):
 * - Form login responsif (email + password)
 * - Auth::attempt() dengan session & cookie
 * - Throttling (maks 5 percobaan gagal per menit)
 * - Generic error message (tidak bocorkan info sensitif)
 * - Redirect ke dashboard sesuai role setelah login berhasil
 */
#[Layout('components.layouts.guest')]
#[Title('Login — PDAM Monitor')]
class Login extends Component
{
    public string $login = '';
    public string $password = '';
    public bool $remember = false;

    /**
     * Aturan validasi form login.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'login' => 'required|string',
            'password' => 'required|string|min:6',
        ];
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'login.required' => 'Email atau username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ];
    }

    /**
     * Proses login dengan throttling dan validasi.
     */
    public function authenticate(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        $fieldType = filter_var($this->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        if (! Auth::attempt(
            [$fieldType => $this->login, 'password' => $this->password],
            $this->remember
        )) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => __('Email/username atau password yang Anda masukkan salah.'),
            ]);
        }

        // Cek apakah akun aktif (soft-disable check, FR-009)
        $user = Auth::user();
        if (! $user->is_active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'login' => __('Akun Anda telah dinonaktifkan. Hubungi Administrator.'),
            ]);
        }

        // Reset throttle counter setelah login berhasil
        RateLimiter::clear($this->throttleKey());

        // Regenerasi session ID untuk mencegah session fixation (NFR-010)
        session()->regenerate();

        // Redirect ke dashboard (FR-005)
        $this->redirectIntended(default: route('dashboard'), navigate: true);
    }

    /**
     * Pastikan user belum melampaui batas percobaan login (FR-003).
     * Maksimal 5 percobaan per menit per kombinasi email+IP.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => __('Terlalu banyak percobaan login. Silakan coba lagi dalam :seconds detik.', [
                'seconds' => $seconds,
            ]),
        ]);
    }

    /**
     * Generate throttle key unik per email/username + IP address.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->login) . '|' . request()->ip());
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
