<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\KomoditasController;
use App\Http\Controllers\Web\HargaController;
use App\Http\Controllers\Web\PrediksiController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\UserHargaController;
use App\Http\Controllers\Web\UserPrediksiController;
use App\Http\Controllers\Web\UserSimulasiController;
use App\Http\Controllers\Web\UserChatAiController;
use App\Http\Controllers\Web\UserProfilController;

// Landing Page
Route::get('/', function () {
    return view('landing');
})->name('landing');

// Alternative route for home
Route::get('/home', function () {
    return redirect()->route('landing');
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


    // Generate Prediksi
    Route::get('/prediksi',            [PrediksiController::class, 'index'])->name('prediksi.index');
    Route::post('/prediksi/generate',  [PrediksiController::class, 'generate'])->name('prediksi.generate');
    Route::get('/prediksi/{id}',       [PrediksiController::class, 'show'])->name('prediksi.show');
    Route::delete('/prediksi/{id}',    [PrediksiController::class, 'destroy'])->name('prediksi.destroy');

        // Profile
        Route::get('/profile',  [AdminController::class, 'profile'])->name('profile');
        Route::put('/profile',  [AdminController::class, 'updateProfile'])->name('profile.update');

});

});


// ── USER ROUTES ───────────────────────────────────────────────
Route::middleware(['role:user'])->group(function () {

    Route::get('/home',      [App\Http\Controllers\Web\UserController::class,       'home'])->name('user.home');
    Route::get('/harga', [HargaController::class, 'userIndex'])->name('user.harga');
    Route::get('/prediksi',  [App\Http\Controllers\Web\UserPrediksiController::class,'prediksi'])->name('user.prediksi');
    Route::get('/simulasi',  [App\Http\Controllers\Web\UserSimulasiController::class,'simulasi'])->name('user.simulasi');
    Route::get('/chatai', [App\Http\Controllers\Web\UserChatAiController::class, 'index'])->name('user.chatai');
    Route::get('/user/profil', [UserProfilController::class, 'index'])->name('user.profil');
    Route::put('/user/profil', [UserProfilController::class, 'update'])->name('user.profil.update');
    Route::put('/user/profil/password', [UserProfilController::class, 'password'])->name('user.profil.password');
});
