<?php

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/low-stock', [ProductController::class, 'lowStock']);

    // Customers
    Route::get('/customers/search', [CustomerController::class, 'search']);
    Route::get('/customers/orders', [CustomerController::class, 'orderHistory']);

    // Orders
    Route::post('/orders', [OrderController::class, 'store']);
});
