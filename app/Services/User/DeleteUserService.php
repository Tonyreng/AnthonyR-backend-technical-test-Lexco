<?php

namespace App\Services\User;

use App\Exceptions\UserDeletionConflictException;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class DeleteUserService
{
    /**
     * Delete an existing managed user.
     *
     * @param User $user
     * @param User $actor
     * @return void
     * @author OpenCode
     * @since 2026/05
     */
    public function handle(User $user, User $actor): void
    {
        if ($actor->is($user)) {
            throw ValidationException::withMessages([
                'user' => ['You cannot delete your own user account.'],
            ]);
        }

        if ($user->purchases()->exists()) {
            throw new UserDeletionConflictException('User cannot be deleted because it has associated history.');
        }

        $user->delete();
    }
}
