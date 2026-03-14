<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\KomoditasController;
use App\Http\Controllers\Web\HargaController;
use App\Http\Controllers\Web\PrediksiController;
use App\Http\Controllers\Web\SettingsController;

Route::get('/', function () {
    return view('welcome');
});


//auth routes
Route::get('/register', [AuthController::class, 'showRegisterUser']);
Route::post('/register', [AuthController::class, 'registerUser']);

Route::get('/register-admin', [AuthController::class, 'showRegisterAdmin']);
Route::post('/register-admin', [AuthController::class, 'registerAdmin']);

Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

//admin routes
Route::get('/admin/dashboard', [App\Http\Controllers\Web\AdminController::class, 'dashboard']);

Route::prefix('admin')->group(function () {

    // Komoditas
    Route::get('/komoditas',           [KomoditasController::class, 'index']);
    Route::get('/komoditas/create',    [KomoditasController::class, 'create']);
    Route::post('/komoditas',          [KomoditasController::class, 'store']);
    Route::get('/komoditas/{id}/edit', [KomoditasController::class, 'edit']);
    Route::put('/komoditas/{id}',      [KomoditasController::class, 'update']);
    Route::delete('/komoditas/{id}',   [KomoditasController::class, 'destroy']);

    // Data Harga
    Route::get('/harga',               [HargaController::class, 'index']);
    Route::get('/harga/create',        [HargaController::class, 'create']);
    Route::post('/harga',              [HargaController::class, 'store']);
    Route::get('/harga/{id}/edit',     [HargaController::class, 'edit']);
    Route::put('/harga/{id}',          [HargaController::class, 'update']);
    Route::delete('/harga/{id}',       [HargaController::class, 'destroy']);

    // Generate Prediksi
    Route::get('/prediksi',            [PrediksiController::class, 'index']);
    Route::post('/prediksi/generate',  [PrediksiController::class, 'generate']);
    Route::get('/prediksi/{id}',       [PrediksiController::class, 'show']);
    Route::delete('/prediksi/{id}',    [PrediksiController::class, 'destroy']);

    // Settings
    Route::get('/settings',            [SettingsController::class, 'index']);
    Route::put('/settings/profile',    [SettingsController::class, 'updateProfile']);
    Route::put('/settings/password',   [SettingsController::class, 'updatePassword']);

});

//user routes
Route::get('/dashboard', [App\Http\Controllers\Web\UserController::class, 'dashboard']);