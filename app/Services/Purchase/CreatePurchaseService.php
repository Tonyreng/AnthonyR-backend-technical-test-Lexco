<?php

namespace App\Services\Purchase;

use App\Exceptions\PurchaseStockConflictException;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class CreatePurchaseService
{
    /**
     * Create a purchase and discount inventory atomically.
     *
     * @param User $user
     * @param array<string, mixed> $data
     * @return Purchase
     * @author OpenCode
     * @since 2026/05
     */
    public function handle(User $user, array $data): Purchase
    {
        /** @var array<int, array{product_id:int, quantity:int}> $items */
        $items = $data['items'];

        return DB::transaction(function () use ($user, $items): Purchase {
            $productIds = array_column($items, 'product_id');

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== count($productIds)) {
                throw (new ModelNotFoundException())->setModel(Product::class);
            }

            $preparedItems = [];
            $totalInCents = 0;

            foreach ($items as $item) {
                /** @var Product $product */
                $product = $products->get($item['product_id']);
                $quantity = $item['quantity'];

                if ($product->stock < $quantity) {
                    throw new PurchaseStockConflictException('Insufficient stock');
                }

                $unitPrice = (string) $product->price;
                $unitPriceInCents = (int) round(((float) $unitPrice) * 100);
                $subtotalInCents = $unitPriceInCents * $quantity;
                $subtotal = number_format($subtotalInCents / 100, 2, '.', '');

                $preparedItems[] = [
                    'product' => $product,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];

                $totalInCents += $subtotalInCents;
            }

            $purchase = Purchase::query()->create([
                'user_id' => $user->id,
                'total' => number_format($totalInCents / 100, 2, '.', ''),
                'status' => 'completed',
            ]);

            foreach ($preparedItems as $preparedItem) {
                $purchase->items()->create([
                    'product_id' => $preparedItem['product_id'],
                    'quantity' => $preparedItem['quantity'],
                    'unit_price' => $preparedItem['unit_price'],
                    'subtotal' => $preparedItem['subtotal'],
                ]);

                /** @var Product $product */
                $product = $preparedItem['product'];
                $product->decrement('stock', $preparedItem['quantity']);
            }

            return $purchase->load('items');
        });
    }
}
