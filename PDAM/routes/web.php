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

    // ── Terbuka untuk semua (hanya butuh auth) ─────────────────────────────
    Route::get('/', ExecutiveDashboard::class)->name('dashboard');

    Route::middleware('permission:view_peta_tekanan|view_peta_valve')->group(function () {
        Route::get('/peta', PetaDistribusi::class)->name('peta-distribusi');
    });

    Route::middleware('can:manage_tekanan')->group(function () {
        Route::get('/log-tekanan', LogTekananMonitor::class)->name('log-tekanan');
        Route::get('/manajemen-daerah-tekanan', ManajemenDaerahTekanan::class)->name('manajemen-daerah-tekanan');
        Route::get('/export/log-tekanan', [ExportController::class, 'logTekanan'])->name('export.log-tekanan');
    });

    Route::middleware('can:manage_valve')->group(function () {
        Route::get('/log-valve', LogValveRiwayat::class)->name('log-valve');
        Route::get('/manajemen-aset', ManajemenAset::class)->name('manajemen-aset');
        Route::get('/export/log-valve', [ExportController::class, 'logValve'])->name('export.log-valve');
    });

    Route::middleware('can:manage_users')->group(function () {
        Route::get('/manajemen-pengguna', ManajemenPengguna::class)->name('manajemen-pengguna');
    });

    Route::middleware('role:super_admin')->group(function () {
        Route::get('/manajemen-role', \App\Livewire\ManajemenRole::class)->name('manajemen-role');
    });
});

/*
|--------------------------------------------------------------------------
| Storage Route Fallback (Fix Docker Windows 403 Forbidden)
|--------------------------------------------------------------------------
*/
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    return response()->file($filePath);
})->where('path', '.*')->name('storage.local');
