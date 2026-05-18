<?php

use App\Http\Controllers\Product\CreateProductController;
use App\Http\Controllers\Product\ListProductsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['session.api', 'auth:web', 'admin'])->group(function () {
    Route::get('/', ListProductsController::class);
    Route::post('/', CreateProductController::class);
});
