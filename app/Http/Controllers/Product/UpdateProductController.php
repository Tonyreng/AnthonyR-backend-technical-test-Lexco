<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use App\Services\Product\UpdateProductService;
use Illuminate\Http\JsonResponse;

class UpdateProductController extends Controller
{
    /**
     * Update an existing managed product.
     *
     * @param UpdateProductRequest $request
     * @param Product $product
     * @param UpdateProductService $updateProductService
     * @return JsonResponse
     * @author OpenCode
     * @since 2026/05
     */
    public function __invoke(UpdateProductRequest $request, Product $product, UpdateProductService $updateProductService): JsonResponse
    {
        $updatedProduct = $updateProductService->handle($product, $request->validated());

        return response()->json([
            'data' => $updatedProduct,
            'message' => 'Product updated successfully',
        ]);
    }
}
