<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class GetProductController extends Controller
{
    /**
     * Retrieve a managed product detail.
     *
     * @param Product $product
     * @return JsonResponse
     * @author OpenCode
     * @since 2026/05
     */
    public function __invoke(Product $product): JsonResponse
    {
        return response()->json([
            'data' => $product,
            'message' => 'Product retrieved successfully',
        ]);
    }
}
