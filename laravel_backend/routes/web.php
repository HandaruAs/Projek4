<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;


Route::get('/', fn () => view('tentang'))->name('home');


Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::post('/filter',    [DashboardController::class, 'filter'])->name('filter');
    Route::get('/chart-data', [DashboardController::class, 'chartData'])->name('chart-data');
    Route::get('/export-pdf', [DashboardController::class, 'exportPdf'])->name('export-pdf');
});



Route::view('/data-harga', 'data-harga.index')->name('data-harga.index');
Route::view('/prediksi',   'prediksi.index')->name('prediksi.index');
Route::view('/simulasi',   'simulasi.index')->name('simulasi.index');
Route::view('/tentang',    'tentang')->name('tentang');
Route::view('/api/docs',   'api-docs')->name('api.docs');



Route::prefix('admin')->name('admin.')->group(function () {
    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
});