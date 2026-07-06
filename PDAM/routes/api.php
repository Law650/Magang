<?php

use App\Http\Controllers\ApiLogTekananController;
use App\Http\Controllers\ApiLogValveController;
use Illuminate\Support\Facades\Route;

// RESTful API endpoints untuk aplikasi mobile teknisi
Route::post('/log-valve', [ApiLogValveController::class, 'store']);
Route::post('/log-tekanan', [ApiLogTekananController::class, 'store']);
