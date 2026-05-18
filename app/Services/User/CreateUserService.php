<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Arr;

class CreateUserService
{
    /**
     * Create a new managed user.
     *
     * @param array<string, mixed> $data
     * @return User
     * @author OpenCode
     * @since 2026/05
     */
    public function handle(array $data): User
    {
        return User::query()->create(
            Arr::only($data, ['name', 'email', 'password', 'role'])
        );
    }
}
