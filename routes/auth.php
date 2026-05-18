<?php

use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::middleware('session.api')->group(function () {
    Route::post('register', RegisterController::class);
});
