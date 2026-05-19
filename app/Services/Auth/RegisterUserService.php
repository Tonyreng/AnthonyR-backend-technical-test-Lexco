<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegisterUserService
{
    /**
     * Register a user and assign the initial role.
     *
     * @param array<string, mixed> $data
     * @return User
     * @author OpenCode
     * @since 2026/05
     */
    public function handle(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $role = User::query()->exists() ? 'user' : 'admin';

            $user = User::query()->create([
                ...Arr::only($data, ['name', 'email', 'password']),
                'role' => $role,
            ]);

            Auth::guard('web')->login($user);

            return $user->fresh();
        });
    }
}
