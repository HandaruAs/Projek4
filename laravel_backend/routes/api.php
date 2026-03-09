<?php
use App\Http\Controllers\Api\PriceHistoryController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommodityController;
use App\Http\Controllers\Api\PredictionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// tes tipis tipis, nanti dihapus aja
Route::get('/test', function () {
    return response()->json([
        'message' => 'API berhasil'
    ]);
});

