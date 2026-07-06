<?php

use App\Http\Controllers\ExportController;
use App\Livewire\ExecutiveDashboard;
use App\Livewire\LogTekananMonitor;
use App\Livewire\LogValveRiwayat;
use App\Livewire\ManajemenAset;
use App\Livewire\PetaDistribusi;
use Illuminate\Support\Facades\Route;

// Dashboard Pages (Livewire full-page components)
Route::get('/', ExecutiveDashboard::class)->name('dashboard');
Route::get('/manajemen-aset', ManajemenAset::class)->name('manajemen-aset');
Route::get('/log-valve', LogValveRiwayat::class)->name('log-valve');
Route::get('/log-tekanan', LogTekananMonitor::class)->name('log-tekanan');
Route::get('/peta', PetaDistribusi::class)->name('peta-distribusi');

// Export endpoints
Route::get('/export/log-valve', [ExportController::class, 'logValve'])->name('export.log-valve');
Route::get('/export/log-tekanan', [ExportController::class, 'logTekanan'])->name('export.log-tekanan');
