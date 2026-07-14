<?php

use App\Http\Controllers\Mobile\ApiAsetController;
use App\Http\Controllers\Mobile\ApiAuthController;
use App\Http\Controllers\Mobile\ApiLogTekananController;
use App\Http\Controllers\Mobile\ApiLogValveController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Endpoint REST API untuk aplikasi mobile teknisi lapangan.
|
*/

// Public routes (Guest)
Route::post('/auth/login', [ApiAuthController::class, 'login']);

// Protected routes (Authenticated via Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Profile
    Route::get('/auth/me', [ApiAuthController::class, 'me']);
    Route::post('/auth/logout', [ApiAuthController::class, 'logout']);

    // Master Data (untuk form dropdown offline-first sinkronisasi)
    Route::get('/aset-valve', [ApiAsetController::class, 'index']);
    Route::post('/aset-valve', [ApiAsetController::class, 'store']);
    Route::get('/lokasi', [ApiAsetController::class, 'lokasi']);
    Route::post('/lokasi-tekanan', [ApiAsetController::class, 'storeLokasiTekanan']);

    // Transaksi / Log
    Route::post('/log-valve', [ApiLogValveController::class, 'store']);
    Route::get('/log-tekanan/rekap', [ApiLogTekananController::class, 'rekap']);
    Route::post('/log-tekanan', [ApiLogTekananController::class, 'store']);
});
