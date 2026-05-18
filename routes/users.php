<?php

use App\Http\Controllers\User\CreateUserController;
use App\Http\Controllers\User\ListUsersController;
use App\Http\Controllers\User\UpdateUserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['session.api', 'auth:web', 'admin'])->group(function () {
    Route::get('/', ListUsersController::class);
    Route::post('/', CreateUserController::class);
    Route::match(['put', 'patch'], '/{user}', UpdateUserController::class);
});
