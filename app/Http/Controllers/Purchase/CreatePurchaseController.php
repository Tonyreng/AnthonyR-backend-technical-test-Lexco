<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Services\Purchase\CreatePurchaseService;
use Illuminate\Http\JsonResponse;

class CreatePurchaseController extends Controller
{
    /**
     * Create a purchase for one or more products.
     *
     * @param StorePurchaseRequest $request
     * @param CreatePurchaseService $createPurchaseService
     * @return JsonResponse
     * @author OpenCode
     * @since 2026/05
     */
    public function __invoke(StorePurchaseRequest $request, CreatePurchaseService $createPurchaseService): JsonResponse
    {
        $purchase = $createPurchaseService->handle($request->user(), $request->validated());

        return response()->json([
            'data' => [
                'id' => $purchase->id,
                'user_id' => $purchase->user_id,
                'total' => $purchase->total,
                'status' => $purchase->status,
                'items' => $purchase->items->map(fn ($item) => [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ])->values()->all(),
            ],
            'message' => 'Purchase completed successfully',
        ], 201);
    }
}
