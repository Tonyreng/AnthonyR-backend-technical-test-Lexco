<?php

namespace App\Services\Catalog;

use App\Models\Product;

class GetAvailableProductService
{
    /**
     * Retrieve a single available product for the authenticated catalog.
     *
     * @param string $productId
     * @return Product
     * @author OpenCode
     * @since 2026/05
     */
    public function handle(string $productId): Product
    {
        return Product::query()
            ->select(['id', 'name', 'description', 'category', 'price', 'stock'])
            ->whereKey($productId)
            ->where('stock', '>', 0)
            ->firstOrFail();
    }
}
