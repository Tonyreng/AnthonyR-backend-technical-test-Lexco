<?php

use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(base_path('routes/auth.php'));
Route::prefix('users')->group(base_path('routes/users.php'));
Route::prefix('products')->group(base_path('routes/products.php'));
