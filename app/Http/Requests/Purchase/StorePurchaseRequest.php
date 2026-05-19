<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    /**
     * Determine if the purchase creation request is authorized.
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
     * Get the validation rules for purchase creation.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     * @author OpenCode
     * @since 2026/05
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
