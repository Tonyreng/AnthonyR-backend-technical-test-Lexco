<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class GetProductController extends Controller
{
    public function __invoke(Product $product): JsonResponse
    {
        return response()->json([
            'data' => $product,
            'message' => 'Product retrieved successfully',
        ]);
    }
}
