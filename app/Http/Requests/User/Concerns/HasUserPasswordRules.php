<?php

namespace App\Http\Requests\User\Concerns;

use Illuminate\Validation\Rules\Password;

trait HasUserPasswordRules
{
    /**
     * Build the shared password validation rules for user management.
     *
     * @return array<int, \Illuminate\Contracts\Validation\ValidationRule|string>
     * @author OpenCode
     * @since 2026/05
     */
    protected function passwordRules(bool $required): array
    {
        return [
            $required ? 'required' : 'sometimes',
            'confirmed',
            Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols(),
        ];
    }
}
