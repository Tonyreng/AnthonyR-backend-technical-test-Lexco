<?php

namespace App\Services\Catalog;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListAvailableProductsService
{
    /**
     * Retrieve paginated available products for the authenticated catalog.
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

        return Product::query()
            ->select(['id', 'name', 'description', 'category', 'price', 'stock'])
            ->where('stock', '>', 0)
            ->when($search, function ($query, string $search) {
                $query->where(function ($productQuery) use ($search): void {
                    $productQuery
                        ->where('name', 'ilike', "%{$search}%")
                        ->orWhere('description', 'ilike', "%{$search}%")
                        ->orWhere('category', 'ilike', "%{$search}%");
                });
            })
            ->when($category, fn ($query, string $category) => $query->where('category', $category))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
