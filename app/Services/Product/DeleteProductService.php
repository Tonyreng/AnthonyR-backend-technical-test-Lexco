<?php

namespace App\Services\Product;

use App\Exceptions\ProductDeletionConflictException;
use App\Models\Product;

class DeleteProductService
{
    /**
     * Delete an existing managed product.
     *
     * @param Product $product
     * @return void
     * @author OpenCode
     * @since 2026/05
     */
    public function handle(Product $product): void
    {
        if ($product->purchaseItems()->exists()) {
            throw new ProductDeletionConflictException('Product cannot be deleted because it has associated purchase history.');
        }

        $product->delete();
    }
}
