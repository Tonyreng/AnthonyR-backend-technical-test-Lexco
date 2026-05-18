<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class IndexProductRequest extends FormRequest
{
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

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
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
