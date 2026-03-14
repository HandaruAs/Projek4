<?php

use App\Http\Controllers\Api\PriceHistoryController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommodityController;
use App\Http\Controllers\Api\PredictionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

// Public routes (no auth required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (auth required)
Route::middleware('auth:api')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Category routes
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Commodity routes
    Route::get('/commodities', [CommodityController::class, 'index']);
    Route::get('/commodities/{id}', [CommodityController::class, 'show']);
    Route::post('/commodities', [CommodityController::class, 'store']);
    Route::put('/commodities/{id}', [CommodityController::class, 'update']);
    Route::delete('/commodities/{id}', [CommodityController::class, 'destroy']);

    // Price History routes
    Route::get('/price-histories', [PriceHistoryController::class, 'index']);
    Route::get('/price-histories/{id}', [PriceHistoryController::class, 'show']);
    Route::post('/price-histories', [PriceHistoryController::class, 'store']);
    Route::post('/price-histories/bulk', [PriceHistoryController::class, 'bulkStore']);
    Route::put('/price-histories/{id}', [PriceHistoryController::class, 'update']);
    Route::delete('/price-histories/{id}', [PriceHistoryController::class, 'destroy']);

    // Prediction routes
    Route::get('/predictions', [PredictionController::class, 'index']);
    Route::get('/predictions/{id}', [PredictionController::class, 'show']);
    Route::get('/predictions/latest', [PredictionController::class, 'latest']);
    Route::get('/predictions/summary', [PredictionController::class, 'summary']);
    Route::delete('/predictions/{id}', [PredictionController::class, 'destroy']);
});

