<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommodityController;
use App\Http\Controllers\Api\PriceHistoryController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\Api\PredictionController;
use App\Http\Controllers\Api\PriceLatestController;
use App\Http\Controllers\Web\UserChatAiController;

// ── Chat AI ─────────────────────────────────────────────
Route::get('/chatai/komoditas',    [UserChatAiController::class, 'komoditas']);
Route::post('/chatai/rekomendasi', [UserChatAiController::class, 'rekomendasi']);
Route::post('/chatai/followup',    [UserChatAiController::class, 'followup']);

// ── Authentication (public) ─────────────────────────────
Route::post('/login',           [AuthController::class, 'login']);
Route::post('/register/user',   [AuthController::class, 'registerUser']);
Route::post('/register/admin',  [AuthController::class, 'registerAdmin']);

// ── Forgot Password Flow (OTP) ──────────────────────────
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-otp',      [AuthController::class, 'verifyOtp']);
Route::post('/reset-password',  [AuthController::class, 'resetPassword']);

// ── Public ───────────────────────────────────────────────
Route::get('/commodities',          [CommodityController::class, 'index']);
Route::get('/commodities/{id}',     [CommodityController::class, 'show']);
Route::get('/categories',           [CategoryController::class, 'index']);
Route::get('/price-histories',      [PriceHistoryController::class, 'index']);
Route::get('/price-histories/{id}', [PriceHistoryController::class, 'show']);
Route::get('/statistics',           [StatisticsController::class, 'index']);
Route::post('/predictions/generate', [PredictionController::class, 'generate']);
// Urutan ini penting — rekomendasi harus di atas {komoditas}
Route::post('/predictions/rekomendasi',         [PredictionController::class, 'rekomendasi']);
Route::get('/predictions',                      [PredictionController::class, 'index']);
Route::get('/predictions/{komoditas}',          [PredictionController::class, 'show']);
Route::get('/prices/latest',        [PriceLatestController::class, 'index']);
Route::get('/prices/categories',    [PriceLatestController::class, 'categories']);
Route::get('/prices/top',           [PriceLatestController::class, 'top']);

// ── Chat AI ─────────────────────────────────────────────
Route::get('/chatai/komoditas',    [UserChatAiController::class, 'komoditas']);
Route::post('/chatai/rekomendasi', [UserChatAiController::class, 'rekomendasi']);
Route::post('/chatai/followup',    [UserChatAiController::class, 'followup']);

// ── Protected: user yang login ───────────────────────────
Route::middleware('auth:api')->group(function () {
    Route::get('/profile',  [AuthController::class, 'getProfile']);
    Route::put('/profile',  [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/logout',  [AuthController::class, 'logout']);
});
