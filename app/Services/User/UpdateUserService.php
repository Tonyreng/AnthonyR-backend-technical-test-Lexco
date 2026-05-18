<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class UpdateUserService
{
    /**
     * Update an existing managed user.
     *
     * @param User $user
     * @param array<string, mixed> $data
     * @param User $actor
     * @return User
     * @author OpenCode
     * @since 2026/05
     */
    public function handle(User $user, array $data, User $actor): User
    {
        if ($actor->is($user) && ($data['role'] ?? null) !== 'admin') {
            throw ValidationException::withMessages([
                'role' => ['You cannot change your own admin role.'],
            ]);
        }

        $user->fill(Arr::only($data, ['name', 'email', 'role']));

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return $user->fresh();
    }
}
