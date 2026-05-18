<?php

use App\Http\Controllers\User\ListUsersController;
use Illuminate\Support\Facades\Route;

Route::middleware(['session.api', 'auth:web', 'admin'])->group(function () {
    Route::get('/', ListUsersController::class);
});
