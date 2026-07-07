<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: Cek apakah akun user yang sedang login masih aktif.
 *
 * Jika is_active = false (soft-disabled oleh Admin via FR-009),
 * user akan di-logout otomatis dan redirect ke halaman login
 * dengan pesan error.
 */
class CheckIsActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && ! Auth::user()->is_active) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Akun Anda telah dinonaktifkan. Hubungi Administrator.']);
        }

        return $next($request);
    }
}
