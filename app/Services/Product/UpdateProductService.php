<?php

namespace App\Services\Product;

use App\Models\Product;
use Illuminate\Support\Arr;

class UpdateProductService
{
    /**
     * Update an existing managed product.
     *
     * @param Product $product
     * @param array<string, mixed> $data
     * @return Product
     * @author OpenCode
     * @since 2026/05
     */
    public function handle(Product $product, array $data): Product
    {
        $product->fill(Arr::only($data, ['name', 'description', 'category', 'price', 'stock']));
        $product->save();

        return $product->fresh();
    }
}
