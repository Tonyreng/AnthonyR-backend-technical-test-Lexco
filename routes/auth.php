<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::middleware('session.api')->group(function () {
    Route::post('login', LoginController::class);
    Route::post('register', RegisterController::class);

    Route::middleware('auth:web')->group(function () {
        Route::get('me', MeController::class);
        Route::post('logout', LogoutController::class);
    });
});
