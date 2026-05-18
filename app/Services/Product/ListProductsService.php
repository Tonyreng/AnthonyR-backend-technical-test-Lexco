<?php

namespace App\Services\Product;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListProductsService
{
    /**
     * Retrieve paginated products with optional filters.
     *
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator
     * @author OpenCode
     * @since 2026/05
     */
    public function handle(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);
        $search = $filters['search'] ?? null;
        $category = $filters['category'] ?? null;
        $inStock = $filters['in_stock'] ?? null;

        return Product::query()
            ->when($search, function ($query, string $search) {
                $query->where(function ($productQuery) use ($search): void {
                    $productQuery
                        ->where('name', 'ilike', "%{$search}%")
                        ->orWhere('description', 'ilike', "%{$search}%")
                        ->orWhere('category', 'ilike', "%{$search}%");
                });
            })
            ->when($category, fn ($query, string $category) => $query->where('category', $category))
            ->when($inStock !== null, function ($query) use ($inStock): void {
                if (filter_var($inStock, FILTER_VALIDATE_BOOLEAN)) {
                    $query->where('stock', '>', 0);

                    return;
                }

                $query->where('stock', 0);
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
