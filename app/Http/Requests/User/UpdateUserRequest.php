<?php

namespace App\Http\Requests\User;

use App\Http\Requests\User\Concerns\HasUserPasswordRules;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    use HasUserPasswordRules;

    /**
     * Determine if the user update request is authorized.
     *
     * @return bool
     * @author OpenCode
     * @since 2026/05
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules for managed user updates.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     * @author OpenCode
     * @since 2026/05
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', ...$this->passwordRules(false)],
            'role' => ['required', 'string', Rule::in(['admin', 'user'])],
        ];
    }

    /**
     * Get custom validation messages for user updates.
     *
     * @return array<string, string>
     * @author OpenCode
     * @since 2026/05
     */
    public function messages(): array
    {
        return [
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
