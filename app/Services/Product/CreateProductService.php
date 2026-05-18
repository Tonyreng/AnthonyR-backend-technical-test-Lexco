<?php

namespace App\Services\Product;

use App\Models\Product;
use Illuminate\Support\Arr;

class CreateProductService
{
    /**
     * Create a new managed product.
     *
     * @param array<string, mixed> $data
     * @return Product
     * @author OpenCode
     * @since 2026/05
     */
    public function handle(array $data): Product
    {
        return Product::query()->create(
            Arr::only($data, ['name', 'description', 'category', 'price', 'stock'])
        );
    }
}
