<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Product\DeleteProductService;
use Illuminate\Http\JsonResponse;

class DeleteProductController extends Controller
{
    /**
     * Delete an existing managed product.
     *
     * @param Product $product
     * @param DeleteProductService $deleteProductService
     * @return JsonResponse
     * @author OpenCode
     * @since 2026/05
     */
    public function __invoke(Product $product, DeleteProductService $deleteProductService): JsonResponse
    {
        $deleteProductService->handle($product);

        return response()->json(status: 204);
    }
}
