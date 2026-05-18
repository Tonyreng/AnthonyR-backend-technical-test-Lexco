<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::middleware('session.api')->group(function () {
    Route::post('login', LoginController::class);
    Route::post('register', RegisterController::class);
});
