<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Services\Product\CreateProductService;
use Illuminate\Http\JsonResponse;

class CreateProductController extends Controller
{
    /**
     * Create a new managed product.
     *
     * @param StoreProductRequest $request
     * @param CreateProductService $createProductService
     * @return JsonResponse
     * @author OpenCode
     * @since 2026/05
     */
    public function __invoke(StoreProductRequest $request, CreateProductService $createProductService): JsonResponse
    {
        $product = $createProductService->handle($request->validated());

        return response()->json([
            'data' => $product,
            'message' => 'Product created successfully',
        ], 201);
    }
}
