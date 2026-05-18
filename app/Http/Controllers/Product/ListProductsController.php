<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\IndexProductRequest;
use App\Services\Product\ListProductsService;
use Illuminate\Http\JsonResponse;

class ListProductsController extends Controller
{
    public function __invoke(IndexProductRequest $request, ListProductsService $listProductsService): JsonResponse
    {
        $products = $listProductsService->handle($request->validated());

        return response()->json([
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
            ],
            'message' => 'Products retrieved successfully',
        ]);
    }
}
