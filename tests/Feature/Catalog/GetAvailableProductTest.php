<?php

namespace Tests\Feature\Catalog;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetAvailableProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_available_product_detail(): void
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
            ->getJson("/api/catalog/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.name', 'Laptop Pro')
            ->assertJsonPath('data.description', 'Laptop de alto rendimiento')
            ->assertJsonPath('data.category', 'electronics')
            ->assertJsonPath('data.price', '1299.99')
            ->assertJsonPath('data.stock', 12)
            ->assertJsonPath('message', 'Available product retrieved successfully');
    }

    public function test_authenticated_admin_can_view_available_product_detail(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $product = Product::query()->create([
            'name' => 'Laptop Pro',
            'description' => 'Laptop de alto rendimiento',
            'category' => 'electronics',
            'price' => 1299.99,
            'stock' => 12,
        ]);

        $this->actingAs($admin, 'web')
            ->getJson("/api/catalog/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.name', 'Laptop Pro');
    }

    public function test_guest_cannot_view_available_product_detail(): void
    {
        $product = Product::query()->create([
            'name' => 'Laptop Pro',
            'description' => 'Laptop de alto rendimiento',
            'category' => 'electronics',
            'price' => 1299.99,
            'stock' => 12,
        ]);

        $this->getJson("/api/catalog/products/{$product->id}")
            ->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_returns_not_found_for_non_existing_product(): void
    {
        $user = User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'Password*123',
            'role' => 'user',
        ]);

        $this->actingAs($user, 'web')
            ->getJson('/api/catalog/products/999999')
            ->assertNotFound()
            ->assertExactJson([
                'message' => 'Product not found',
            ]);
    }

    public function test_returns_not_found_for_product_without_stock(): void
    {
        $user = User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'Password*123',
            'role' => 'user',
        ]);

        $product = Product::query()->create([
            'name' => 'Office Chair',
            'description' => 'Silla ergonomica',
            'category' => 'furniture',
            'price' => 299.99,
            'stock' => 0,
        ]);

        $this->actingAs($user, 'web')
            ->getJson("/api/catalog/products/{$product->id}")
            ->assertNotFound()
            ->assertExactJson([
                'message' => 'Product not found',
            ]);
    }

    public function test_available_product_detail_does_not_return_internal_fields(): void
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

        $response = $this->actingAs($user, 'web')->getJson("/api/catalog/products/{$product->id}");

        $response->assertOk();
        $response->assertJsonMissingPath('data.created_at');
        $response->assertJsonMissingPath('data.updated_at');
        $response->assertJsonMissingPath('data.purchase_items');
    }
}
