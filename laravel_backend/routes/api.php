<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommodityController;
use App\Http\Controllers\Api\PriceHistoryController;
use App\Http\Controllers\Api\PredictionController;

Route::get('/test', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API Laravel berhasil diakses',
        'server_time' => now()
    ]);
});

// Handle API routes for authentication
Route::post('/login', [AuthController::class,'login']);

Route::post('/register/user', [AuthController::class,'registerUser']);
Route::post('/register/admin', [AuthController::class,'registerAdmin']);
Route::middleware('auth:api')->post('/logout', [AuthController::class,'logout']);
