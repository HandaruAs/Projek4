<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;

Route::get('/', function () {
    return view('welcome');
});


//auth routes
Route::get('/register', [AuthController::class, 'showRegisterUser']);
Route::post('/register', [AuthController::class, 'registerUser']);

Route::get('/register-admin', [AuthController::class, 'showRegisterAdmin']);
Route::post('/register-admin', [AuthController::class, 'registerAdmin']);

Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

//admin routes
Route::get('/admin/dashboard', [App\Http\Controllers\Web\AdminController::class, 'dashboard']);

//user routes
Route::get('/dashboard', [App\Http\Controllers\Web\UserController::class, 'dashboard']);
