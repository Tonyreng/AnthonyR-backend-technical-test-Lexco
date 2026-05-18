<?php

namespace Tests\Feature\Catalog;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListAvailableProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_available_products(): void
    {
        $user = User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'Password*123',
            'role' => 'user',
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

        $this->actingAs($user, 'web')
            ->getJson('/api/catalog/products')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Laptop Pro')
            ->assertJsonMissing([
                'name' => 'Office Chair',
            ])
            ->assertJsonPath('message', 'Available products retrieved successfully');
    }

    public function test_authenticated_admin_can_list_available_products(): void
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

        $this->actingAs($admin, 'web')
            ->getJson('/api/catalog/products')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Laptop Pro');
    }

    public function test_guest_cannot_list_available_products(): void
    {
        $this->getJson('/api/catalog/products')
            ->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_catalog_returns_empty_paginated_response_when_no_products_are_available(): void
    {
        $user = User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'Password*123',
            'role' => 'user',
        ]);

        Product::query()->create([
            'name' => 'Office Chair',
            'description' => 'Silla ergonomica',
            'category' => 'furniture',
            'price' => 299.99,
            'stock' => 0,
        ]);

        $this->actingAs($user, 'web')
            ->getJson('/api/catalog/products')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.total', 0)
            ->assertJsonPath('meta.last_page', 1);
    }

    public function test_catalog_can_filter_products_by_search_and_category(): void
    {
        $user = User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'Password*123',
            'role' => 'user',
        ]);

        Product::query()->create([
            'name' => 'Laptop Pro',
            'description' => 'Laptop de alto rendimiento',
            'category' => 'electronics',
            'price' => 1299.99,
            'stock' => 12,
        ]);

        Product::query()->create([
            'name' => 'Desk Lamp',
            'description' => 'Lampara para escritorio',
            'category' => 'lighting',
            'price' => 49.99,
            'stock' => 5,
        ]);

        $this->actingAs($user, 'web')
            ->getJson('/api/catalog/products?search=laptop&category=electronics')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Laptop Pro')
            ->assertJsonPath('data.0.category', 'electronics');
    }

    public function test_catalog_does_not_return_created_or_updated_at_fields(): void
    {
        $user = User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'Password*123',
            'role' => 'user',
        ]);

        Product::query()->create([
            'name' => 'Laptop Pro',
            'description' => 'Laptop de alto rendimiento',
            'category' => 'electronics',
            'price' => 1299.99,
            'stock' => 12,
        ]);

        $response = $this->actingAs($user, 'web')->getJson('/api/catalog/products');

        $response->assertJsonMissingPath('data.0.created_at');
        $response->assertJsonMissingPath('data.0.updated_at');
    }

    public function test_catalog_validates_query_params(): void
    {
        $user = User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'Password*123',
            'role' => 'user',
        ]);

        $this->actingAs($user, 'web')
            ->getJson('/api/catalog/products?per_page=101&page=0')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page', 'page']);
    }
}
