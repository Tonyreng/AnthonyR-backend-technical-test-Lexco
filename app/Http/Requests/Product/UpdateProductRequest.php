<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the product update request is authorized.
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
     * Get the validation rules for managed product updates.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     * @author OpenCode
     * @since 2026/05
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string', 'max:1000'],
            'category' => ['sometimes', 'required', 'string', 'max:255'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
        ];
    }

    /**
     * Add post-validation rules for product updates.
     *
     * @param Validator $validator
     * @return void
     * @author OpenCode
     * @since 2026/05
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->hasAny(['name', 'description', 'category', 'price', 'stock'])) {
                return;
            }

            $validator->errors()->add('product', 'At least one product field must be provided.');
        });
    }
}
