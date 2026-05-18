<?php

namespace Tests\Feature\Purchase;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatePurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_purchase_a_single_product(): void
    {
        $user = User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'Password*123',
            'role' => 'user',
        ]);

        $product = Product::query()->create([
            'name' => 'Laptop Pro',
            'description' => 'Laptop de alto rendimiento',
            'category' => 'electronics',
            'price' => 1299.99,
            'stock' => 12,
        ]);

        $this->actingAs($user, 'web')
            ->postJson('/api/purchases', [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.total', '2599.98')
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.items.0.product_id', $product->id)
            ->assertJsonPath('data.items.0.quantity', 2)
            ->assertJsonPath('data.items.0.unit_price', '1299.99')
            ->assertJsonPath('data.items.0.subtotal', '2599.98')
            ->assertJsonPath('message', 'Purchase completed successfully');

        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'total' => '2599.98',
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('purchase_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => '1299.99',
            'subtotal' => '2599.98',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 10,
        ]);
    }

    public function test_authenticated_admin_can_purchase_multiple_products(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $laptop = Product::query()->create([
            'name' => 'Laptop Pro',
            'description' => 'Laptop de alto rendimiento',
            'category' => 'electronics',
            'price' => 1299.99,
            'stock' => 12,
        ]);

        $lamp = Product::query()->create([
            'name' => 'Desk Lamp',
            'description' => 'Lampara para escritorio',
            'category' => 'lighting',
            'price' => 49.99,
            'stock' => 5,
        ]);

        $this->actingAs($admin, 'web')
            ->postJson('/api/purchases', [
                'items' => [
                    [
                        'product_id' => $laptop->id,
                        'quantity' => 2,
                    ],
                    [
                        'product_id' => $lamp->id,
                        'quantity' => 1,
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.user_id', $admin->id)
            ->assertJsonPath('data.total', '2649.97')
            ->assertJsonPath('data.items.0.product_id', $laptop->id)
            ->assertJsonPath('data.items.1.product_id', $lamp->id)
            ->assertJsonPath('data.items.1.subtotal', '49.99');

        $this->assertDatabaseCount('purchases', 1);
        $this->assertDatabaseCount('purchase_items', 2);
        $this->assertDatabaseHas('products', [
            'id' => $laptop->id,
            'stock' => 10,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $lamp->id,
            'stock' => 4,
        ]);
    }

    public function test_guest_cannot_create_purchases(): void
    {
        $this->postJson('/api/purchases', [
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 1,
                ],
            ],
        ])
            ->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_purchase_validates_required_items_and_quantity(): void
    {
        $user = User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'Password*123',
            'role' => 'user',
        ]);

        $response = $this->actingAs($user, 'web')->postJson('/api/purchases', [
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 0,
                ],
            ],
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items.0.quantity']);
    }

    public function test_purchase_rejects_duplicate_products_in_payload(): void
    {
        $user = User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'Password*123',
            'role' => 'user',
        ]);

        $product = Product::query()->create([
            'name' => 'Laptop Pro',
            'description' => 'Laptop de alto rendimiento',
            'category' => 'electronics',
            'price' => 1299.99,
            'stock' => 12,
        ]);

        $response = $this->actingAs($user, 'web')->postJson('/api/purchases', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items.1.product_id']);
    }

    public function test_purchase_returns_not_found_when_one_product_does_not_exist_and_keeps_stock_unchanged(): void
    {
        $user = User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'Password*123',
            'role' => 'user',
        ]);

        $product = Product::query()->create([
            'name' => 'Laptop Pro',
            'description' => 'Laptop de alto rendimiento',
            'category' => 'electronics',
            'price' => 1299.99,
            'stock' => 12,
        ]);

        $this->actingAs($user, 'web')
            ->postJson('/api/purchases', [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                    ],
                    [
                        'product_id' => 999999,
                        'quantity' => 1,
                    ],
                ],
            ])
            ->assertNotFound()
            ->assertExactJson([
                'message' => 'Product not found',
            ]);

        $this->assertDatabaseCount('purchases', 0);
        $this->assertDatabaseCount('purchase_items', 0);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 12,
        ]);
    }

    public function test_purchase_returns_conflict_when_stock_is_insufficient_and_rolls_back_all_changes(): void
    {
        $user = User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'Password*123',
            'role' => 'user',
        ]);

        $laptop = Product::query()->create([
            'name' => 'Laptop Pro',
            'description' => 'Laptop de alto rendimiento',
            'category' => 'electronics',
            'price' => 1299.99,
            'stock' => 12,
        ]);

        $lamp = Product::query()->create([
            'name' => 'Desk Lamp',
            'description' => 'Lampara para escritorio',
            'category' => 'lighting',
            'price' => 49.99,
            'stock' => 1,
        ]);

        $this->actingAs($user, 'web')
            ->postJson('/api/purchases', [
                'items' => [
                    [
                        'product_id' => $laptop->id,
                        'quantity' => 1,
                    ],
                    [
                        'product_id' => $lamp->id,
                        'quantity' => 2,
                    ],
                ],
            ])
            ->assertConflict()
            ->assertExactJson([
                'message' => 'Insufficient stock',
            ]);

        $this->assertDatabaseCount('purchases', 0);
        $this->assertDatabaseCount('purchase_items', 0);
        $this->assertDatabaseHas('products', [
            'id' => $laptop->id,
            'stock' => 12,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $lamp->id,
            'stock' => 1,
        ]);
    }
}
