<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;


Route::get('/', function () {
    return redirect('/login');
});
Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/store', function (Request $request) {
    if (!$request->session()->has('supabase_access_token')) {
        return redirect('/login');
    }

    return view('store');
});
