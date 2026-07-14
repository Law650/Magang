<?php

use App\Http\Controllers\ExportController;
use App\Livewire\Auth\Login;
use App\Livewire\ExecutiveDashboard;
use App\Livewire\LogTekananMonitor;
use App\Livewire\LogValveRiwayat;
use App\Livewire\ManajemenAset;
use App\Livewire\ManajemenDaerahTekanan;
use App\Livewire\ManajemenPengguna;
use App\Livewire\PetaDistribusi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Routes (Guest Only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/
Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Protected Routes (Authenticated Users Only)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check.active'])->group(function () {

    // ── Semua Role (Admin & Pekerja) ─────────────────────────────
    Route::get('/', ExecutiveDashboard::class)->name('dashboard');
    Route::get('/log-valve', LogValveRiwayat::class)->name('log-valve');
    Route::get('/log-tekanan', LogTekananMonitor::class)->name('log-tekanan');
    Route::get('/peta', PetaDistribusi::class)->name('peta-distribusi');
    Route::get('/manajemen-aset', ManajemenAset::class)->name('manajemen-aset');
    Route::get('/manajemen-daerah-tekanan', ManajemenDaerahTekanan::class)->name('manajemen-daerah-tekanan');

    // Export endpoints
    Route::get('/export/log-valve', [ExportController::class, 'logValve'])->name('export.log-valve');
    Route::get('/export/log-tekanan', [ExportController::class, 'logTekanan'])->name('export.log-tekanan');

    // ── Admin Only ───────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('/manajemen-pengguna', ManajemenPengguna::class)->name('manajemen-pengguna');
    });
});
