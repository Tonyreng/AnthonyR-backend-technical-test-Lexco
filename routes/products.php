<?php

use App\Http\Controllers\Product\CreateProductController;
use App\Http\Controllers\Product\ListProductsController;
use App\Http\Controllers\Product\UpdateProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['session.api', 'auth:web', 'admin'])->group(function () {
    Route::get('/', ListProductsController::class);
    Route::post('/', CreateProductController::class);
    Route::match(['put', 'patch'], '/{product}', UpdateProductController::class);
});
