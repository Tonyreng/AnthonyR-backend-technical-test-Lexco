<?php

use App\Http\Controllers\Catalog\GetAvailableProductController;
use App\Http\Controllers\Catalog\ListAvailableProductsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['session.api', 'auth:web'])->group(function () {
    Route::get('/products', ListAvailableProductsController::class);
    Route::get('/products/{product}', GetAvailableProductController::class);
});
