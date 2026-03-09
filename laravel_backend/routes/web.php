<?php

use Illuminate\Support\Facades\Route;
use App\Models\PriceHistory;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/testmongo', function () {
    return PriceHistory::limit(10)->get();
});
