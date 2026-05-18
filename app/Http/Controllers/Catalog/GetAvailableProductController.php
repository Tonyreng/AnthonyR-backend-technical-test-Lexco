<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Services\Catalog\GetAvailableProductService;
use Illuminate\Http\JsonResponse;

class GetAvailableProductController extends Controller
{
    /**
     * Retrieve an available product from the authenticated catalog.
     *
     * @param string $product
     * @param GetAvailableProductService $getAvailableProductService
     * @return JsonResponse
     * @author OpenCode
     * @since 2026/05
     */
    public function __invoke(string $product, GetAvailableProductService $getAvailableProductService): JsonResponse
    {
        $availableProduct = $getAvailableProductService->handle($product);

        return response()->json([
            'data' => $availableProduct,
            'message' => 'Available product retrieved successfully',
        ]);
    }
}
