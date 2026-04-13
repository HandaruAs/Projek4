<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\KomoditasController;
use App\Http\Controllers\Web\HargaController;
use App\Http\Controllers\Web\PrediksiController;
use App\Http\Controllers\Web\SettingsController;


Route::get('/', fn () => view('tentang'))->name('home');


Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::post('/filter',    [DashboardController::class, 'filter'])->name('filter');
    Route::get('/chart-data', [DashboardController::class, 'chartData'])->name('chart-data');
    Route::get('/export-pdf', [DashboardController::class, 'exportPdf'])->name('export-pdf');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
});
// ── AUTH ROUTES ──────────────────────────────────────────────
Route::get('/register',        [AuthController::class, 'showRegisterUser'])->name('register');
Route::post('/register',       [AuthController::class, 'registerUser']);

Route::get('/register-admin',  [AuthController::class, 'showRegisterAdmin'])->name('register.admin');
Route::post('/register-admin', [AuthController::class, 'registerAdmin']);

Route::get('/login',           [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',          [AuthController::class, 'login']);
Route::post('/logout',         [AuthController::class, 'logout'])->name('logout');

Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('forgot.password');
Route::post('/forgot-password', [AuthController::class, 'sendOtp']);

Route::get('/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('verify.otp');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('reset.password');
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// ── ADMIN ROUTES ─────────────────────────────────────────────
// [+] Tambah middleware 'role:admin' — semua route admin wajib login & punya role admin
Route::middleware(['role:admin'])->group(function () {

    Route::get('/admin/dashboard', [App\Http\Controllers\Web\AdminController::class, 'dashboard'])->name('dashboard');

    Route::prefix('admin')->group(function () {

        // Komoditas
        Route::get('/komoditas',           [KomoditasController::class, 'index'])->name('komoditas.index');
        Route::get('/komoditas/create',    [KomoditasController::class, 'create'])->name('komoditas.create');
        Route::post('/komoditas',          [KomoditasController::class, 'store'])->name('komoditas.store');
        Route::get('/komoditas/{id}/edit', [KomoditasController::class, 'edit'])->name('komoditas.edit');
        Route::put('/komoditas/{id}',      [KomoditasController::class, 'update'])->name('komoditas.update');
        Route::delete('/komoditas/{id}',   [KomoditasController::class, 'destroy'])->name('komoditas.destroy');

        // Data Harga
        Route::get('/harga',               [HargaController::class, 'index'])->name('harga.index');
        Route::get('/harga/create',        [HargaController::class, 'create'])->name('harga.create');
        Route::post('/harga',              [HargaController::class, 'store'])->name('harga.store');
        Route::get('/harga/{id}/edit',     [HargaController::class, 'edit'])->name('harga.edit');
        Route::put('/harga/{id}',          [HargaController::class, 'update'])->name('harga.update');
        Route::delete('/harga/{id}',       [HargaController::class, 'destroy'])->name('harga.destroy');

        // Generate Prediksi
        Route::get('/prediksi',            [PrediksiController::class, 'index'])->name('prediksi.index');
        Route::post('/prediksi/generate',  [PrediksiController::class, 'generate'])->name('prediksi.generate');
        Route::get('/prediksi/{id}',       [PrediksiController::class, 'show'])->name('prediksi.show');
        Route::delete('/prediksi/{id}',    [PrediksiController::class, 'destroy'])->name('prediksi.destroy');

        // Settings
        Route::get('/settings',            [SettingsController::class, 'index'])->name('settings');
        Route::put('/settings/profile',    [SettingsController::class, 'updateProfile'])->name('settings.profile');
        Route::put('/settings/password',   [SettingsController::class, 'updatePassword'])->name('settings.password');

    });

}); // [+] tutup middleware admin


// ── USER ROUTES ───────────────────────────────────────────────
// [+] Tambah middleware 'role:user' — hanya user biasa yang boleh akses
Route::middleware(['role:user'])->group(function () {

    Route::get('/dashboard', [App\Http\Controllers\Web\UserController::class, 'dashboard'])->name('user.dashboard');

}); // [+] tutup middleware user
