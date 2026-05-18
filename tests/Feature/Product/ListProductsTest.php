<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_products_with_expected_fields(): void
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

        $response = $this->actingAs($admin, 'web')->getJson('/api/products');

        $response
            ->assertOk()
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.last_page', 1)
            ->assertJsonFragment([
                'id' => $product->id,
                'name' => 'Laptop Pro',
                'category' => 'electronics',
                'stock' => 12,
            ])
            ->assertJsonPath('message', 'Products retrieved successfully');
    }

    public function test_admin_gets_empty_paginated_response_when_no_products_exist(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'web')->getJson('/api/products');

        $response
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.total', 0)
            ->assertJsonPath('meta.last_page', 1);
    }

    public function test_admin_can_filter_products_by_search_category_and_stock(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        Product::query()->create([
            'name' => 'Laptop Pro',
            'description' => 'Laptop de alto rendimiento',
            'category' => 'electronics',
            'price' => 1299.99,
            'stock' => 12,
        ]);

        Product::query()->create([
            'name' => 'Office Chair',
            'description' => 'Silla ergonomica',
            'category' => 'furniture',
            'price' => 299.99,
            'stock' => 0,
        ]);

        $response = $this->actingAs($admin, 'web')
            ->getJson('/api/products?search=laptop&category=electronics&in_stock=true');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Laptop Pro')
            ->assertJsonPath('data.0.category', 'electronics')
            ->assertJsonPath('data.0.stock', 12);
    }

    public function test_admin_can_filter_out_of_stock_products(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        Product::query()->create([
            'name' => 'Laptop Pro',
            'description' => 'Laptop de alto rendimiento',
            'category' => 'electronics',
            'price' => 1299.99,
            'stock' => 12,
        ]);

        Product::query()->create([
            'name' => 'Office Chair',
            'description' => 'Silla ergonomica',
            'category' => 'furniture',
            'price' => 299.99,
            'stock' => 0,
        ]);

        $response = $this->actingAs($admin, 'web')
            ->getJson('/api/products?in_stock=false');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Office Chair')
            ->assertJsonPath('data.0.stock', 0);
    }

    public function test_guest_cannot_list_products(): void
    {
        $this->getJson('/api/products')
            ->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_non_admin_user_cannot_list_products(): void
    {
        $user = User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'Password*123',
            'role' => 'user',
        ]);

        $this->actingAs($user, 'web')
            ->getJson('/api/products')
            ->assertForbidden()
            ->assertExactJson([
                'message' => 'Forbidden.',
            ]);
    }

    public function test_admin_cannot_request_invalid_product_filters(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'web')
            ->getJson('/api/products?per_page=101&in_stock=invalid&page=0');

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page', 'in_stock', 'page']);
    }
}
