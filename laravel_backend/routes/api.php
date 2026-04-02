<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommodityController;
use App\Http\Controllers\Api\PriceHistoryController;
use App\Http\Controllers\Api\PredictionController;

// ── Authentication ──────────────────────────────────────
Route::post('/login',           [AuthController::class, 'login']);
Route::post('/register/user',   [AuthController::class, 'registerUser']);
Route::post('/register/admin',  [AuthController::class, 'registerAdmin']);

// ── Forgot Password Flow (OTP) ──────────────────────────
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']); // 1. Kirim OTP
Route::post('/verify-otp',      [AuthController::class, 'verifyOtp']);       // 2. Verifikasi OTP
Route::post('/reset-password',  [AuthController::class, 'resetPassword']);   // 3. Set password baru

// ── Protected Routes ────────────────────────────────────
Route::middleware('auth:api')->post('/logout', [AuthController::class, 'logout']);
