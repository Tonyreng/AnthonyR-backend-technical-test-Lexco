<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManageProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_product(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'web')->postJson('/api/products', [
            'name' => 'Laptop Pro',
            'description' => 'Laptop de alto rendimiento',
            'category' => 'electronics',
            'price' => 1299.99,
            'stock' => 12,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Laptop Pro')
            ->assertJsonPath('data.description', 'Laptop de alto rendimiento')
            ->assertJsonPath('data.category', 'electronics')
            ->assertJsonPath('data.stock', 12)
            ->assertJsonPath('message', 'Product created successfully');

        $this->assertDatabaseHas('products', [
            'name' => 'Laptop Pro',
            'category' => 'electronics',
            'stock' => 12,
        ]);
    }

    public function test_admin_can_create_a_product_with_zero_stock_and_zero_price(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'web')->postJson('/api/products', [
            'name' => 'Free Sample',
            'description' => 'Producto promocional',
            'category' => 'samples',
            'price' => 0,
            'stock' => 0,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Free Sample')
            ->assertJsonPath('data.stock', 0)
            ->assertJsonPath('data.price', '0.00');

        $this->assertDatabaseHas('products', [
            'name' => 'Free Sample',
            'stock' => 0,
        ]);
    }

    public function test_guest_cannot_create_products(): void
    {
        $this->postJson('/api/products', [
            'name' => 'Laptop Pro',
            'description' => 'Laptop de alto rendimiento',
            'category' => 'electronics',
            'price' => 1299.99,
            'stock' => 12,
        ])
            ->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_non_admin_user_cannot_create_products(): void
    {
        $user = User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'Password*123',
            'role' => 'user',
        ]);

        $this->actingAs($user, 'web')
            ->postJson('/api/products', [
                'name' => 'Laptop Pro',
                'description' => 'Laptop de alto rendimiento',
                'category' => 'electronics',
                'price' => 1299.99,
                'stock' => 12,
            ])
            ->assertForbidden()
            ->assertExactJson([
                'message' => 'Forbidden.',
            ]);
    }

    public function test_admin_cannot_create_product_with_invalid_data(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'web')->postJson('/api/products', [
            'name' => '',
            'description' => '',
            'category' => '',
            'price' => -10,
            'stock' => -1.5,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'description', 'category', 'price', 'stock']);
    }

    public function test_admin_cannot_create_product_without_required_fields(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'web')->postJson('/api/products', []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'description', 'category', 'price', 'stock']);
    }

    public function test_admin_cannot_create_product_with_non_integer_stock(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'web')->postJson('/api/products', [
            'name' => 'Laptop Pro',
            'description' => 'Laptop de alto rendimiento',
            'category' => 'electronics',
            'price' => 1299.99,
            'stock' => 1.5,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['stock']);
    }

    public function test_admin_can_list_products_after_creating_one(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $this->actingAs($admin, 'web')->postJson('/api/products', [
            'name' => 'Laptop Pro',
            'description' => 'Laptop de alto rendimiento',
            'category' => 'electronics',
            'price' => 1299.99,
            'stock' => 12,
        ])->assertCreated();

        $this->actingAs($admin, 'web')
            ->getJson('/api/products')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Laptop Pro');
    }

    public function test_admin_can_update_a_product_with_all_fields(): void
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

        $response = $this->actingAs($admin, 'web')->putJson("/api/products/{$product->id}", [
            'name' => 'Laptop Pro Updated',
            'description' => 'Laptop actualizada de alto rendimiento',
            'category' => 'computers',
            'price' => 1199.99,
            'stock' => 8,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.name', 'Laptop Pro Updated')
            ->assertJsonPath('data.description', 'Laptop actualizada de alto rendimiento')
            ->assertJsonPath('data.category', 'computers')
            ->assertJsonPath('data.price', '1199.99')
            ->assertJsonPath('data.stock', 8)
            ->assertJsonPath('message', 'Product updated successfully');
    }

    public function test_admin_can_update_a_product_partially(): void
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

        $response = $this->actingAs($admin, 'web')->patchJson("/api/products/{$product->id}", [
            'stock' => 0,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.name', 'Laptop Pro')
            ->assertJsonPath('data.stock', 0)
            ->assertJsonPath('data.price', '1299.99');
    }

    public function test_admin_can_update_a_product_price_to_zero(): void
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

        $response = $this->actingAs($admin, 'web')->patchJson("/api/products/{$product->id}", [
            'price' => 0,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.price', '0.00');
    }

    public function test_guest_cannot_update_products(): void
    {
        $product = Product::query()->create([
            'name' => 'Laptop Pro',
            'description' => 'Laptop de alto rendimiento',
            'category' => 'electronics',
            'price' => 1299.99,
            'stock' => 12,
        ]);

        $this->putJson("/api/products/{$product->id}", [
            'stock' => 10,
        ])
            ->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_non_admin_user_cannot_update_products(): void
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
            ->putJson("/api/products/{$product->id}", [
                'stock' => 10,
            ])
            ->assertForbidden()
            ->assertExactJson([
                'message' => 'Forbidden.',
            ]);
    }

    public function test_admin_cannot_update_product_with_empty_payload(): void
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
            ->patchJson("/api/products/{$product->id}", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['product'])
            ->assertJsonPath('errors.product.0', 'At least one product field must be provided.');
    }

    public function test_admin_cannot_update_product_with_invalid_values(): void
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
            ->patchJson("/api/products/{$product->id}", [
                'price' => -10,
                'stock' => 1.5,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['price', 'stock']);
    }

    public function test_admin_gets_not_found_when_updating_missing_product(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $this->actingAs($admin, 'web')
            ->patchJson('/api/products/999999', [
                'stock' => 5,
            ])
            ->assertNotFound()
            ->assertExactJson([
                'message' => 'Product not found',
            ]);
    }
}
