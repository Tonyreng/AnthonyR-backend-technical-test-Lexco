<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class IndexProductRequest extends FormRequest
{
    /**
     * Normalize supported product filter values before validation.
     *
     * @return void
     * @author OpenCode
     * @since 2026/05
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('in_stock')) {
            return;
        }

        $inStock = $this->query('in_stock');

        if ($inStock === 'true') {
            $this->merge(['in_stock' => true]);

            return;
        }

        if ($inStock === 'false') {
            $this->merge(['in_stock' => false]);
        }
    }

    /**
     * Determine if the product listing request is authorized.
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
     * Get the validation rules for product listing filters.
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
            'category' => ['sometimes', 'string'],
            'in_stock' => ['sometimes', 'boolean'],
        ];
    }
}
