<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommodityController;
use App\Http\Controllers\Api\PriceHistoryController;
use App\Http\Controllers\Api\PredictionController;

// ── Authentication (public) ─────────────────────────────
Route::post('/login',           [AuthController::class, 'login']);
Route::post('/register/user',   [AuthController::class, 'registerUser']);
Route::post('/register/admin',  [AuthController::class, 'registerAdmin']);

// ── Forgot Password Flow (OTP) ──────────────────────────
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-otp',      [AuthController::class, 'verifyOtp']);
Route::post('/reset-password',  [AuthController::class, 'resetPassword']);

// ── Public (semua bisa akses tanpa login) ───────────────
Route::get('/commodities',          [CommodityController::class, 'index']);
Route::get('/commodities/{id}',     [CommodityController::class, 'show']);
Route::get('/categories',           [CategoryController::class, 'index']);
Route::get('/price-histories',      [PriceHistoryController::class, 'index']);
Route::get('/price-histories/{id}', [PriceHistoryController::class, 'show']);
Route::get('/predictions',          [PredictionController::class, 'index']);
Route::get('/predictions/{id}',     [PredictionController::class, 'show']);

// ── Protected: semua user yang login ────────────────────
Route::middleware('auth:api')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // ── Admin only ───────────────────────────────────────
    Route::middleware('role:admin')->group(function () {

        // Commodities
        Route::post('/commodities',        [CommodityController::class, 'store']);
        Route::put('/commodities/{id}',    [CommodityController::class, 'update']);
        Route::delete('/commodities/{id}', [CommodityController::class, 'destroy']);

        // Categories
        Route::post('/categories',        [CategoryController::class, 'store']);
        Route::put('/categories/{id}',    [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        // Price Histories
        Route::post('/price-histories',        [PriceHistoryController::class, 'store']);
        Route::put('/price-histories/{id}',    [PriceHistoryController::class, 'update']);
        Route::delete('/price-histories/{id}', [PriceHistoryController::class, 'destroy']);

        // Predictions
        Route::post('/predictions/generate',   [PredictionController::class, 'generate']);
        Route::delete('/predictions/{id}',     [PredictionController::class, 'destroy']);

    });

});
