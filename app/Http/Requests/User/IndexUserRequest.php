<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexUserRequest extends FormRequest
{
    /**
     * Determine if the user listing request is authorized.
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
     * Get the validation rules for user listing filters.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     * @author OpenCode
     * @since 2026/05
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'string'],
            'role' => ['sometimes', 'string', Rule::in(['admin', 'user'])],
        ];
    }
}
