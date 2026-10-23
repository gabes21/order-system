<?php
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'supabase.api.auth'])->group(function () {
    Route::get('/products', [ProductController::class, 'index']);

    Route::get('/products/{slug}', [ProductController::class, 'show']);

    Route::post('/orders', [ProductController::class, 'order']);
});
