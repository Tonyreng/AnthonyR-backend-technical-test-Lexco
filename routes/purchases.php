<?php

use App\Http\Controllers\Purchase\CreatePurchaseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['session.api', 'auth:web'])->group(function () {
    Route::post('/', CreatePurchaseController::class);
});
