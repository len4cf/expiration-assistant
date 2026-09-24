<?php

use App\Http\Controllers\ProductController;
use App\Http\Middleware\ActAsDefaultUser;
use Illuminate\Support\Facades\Route;

Route::get('/ping', fn () => ['message' => 'oi']);

Route::middleware(ActAsDefaultUser::class)->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/expiring', [ProductController::class, 'expiring']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::patch('/products/{product}/status', [ProductController::class, 'updateStatus']);
});
