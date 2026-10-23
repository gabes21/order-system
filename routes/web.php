<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect('/login');
});
Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('supabase.auth')->group(function () {
    Route::get('/store', [ProductController::class, 'store']);

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
