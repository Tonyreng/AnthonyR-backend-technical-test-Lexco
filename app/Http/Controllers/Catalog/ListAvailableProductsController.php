<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\IndexAvailableProductRequest;
use App\Services\Catalog\ListAvailableProductsService;
use Illuminate\Http\JsonResponse;

class ListAvailableProductsController extends Controller
{
    /**
     * Retrieve paginated available products for the catalog.
     *
     * @param IndexAvailableProductRequest $request
     * @param ListAvailableProductsService $listAvailableProductsService
     * @return JsonResponse
     * @author OpenCode
     * @since 2026/05
     */
    public function __invoke(IndexAvailableProductRequest $request, ListAvailableProductsService $listAvailableProductsService): JsonResponse
    {
        $products = $listAvailableProductsService->handle($request->validated());

        return response()->json([
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
            ],
            'message' => 'Available products retrieved successfully',
        ]);
    }
}
