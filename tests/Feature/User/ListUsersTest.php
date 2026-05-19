<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_users_with_safe_fields(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $user = User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'Password*123',
            'role' => 'user',
        ]);

        $response = $this->actingAs($admin, 'web')->getJson('/api/users');

        $response
            ->assertOk()
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.last_page', 1)
            ->assertJsonFragment([
                'id' => $user->id,
                'email' => 'user@example.com',
                'role' => 'user',
            ]);

        $response->assertJsonMissingPath('data.0.password');
        $response->assertJsonMissingPath('data.0.remember_token');
    }

    public function test_guest_cannot_list_users(): void
    {
        $this->getJson('/api/users')
            ->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_non_admin_user_cannot_list_users(): void
    {
        $user = User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'Password*123',
            'role' => 'user',
        ]);

        $this->actingAs($user, 'web')
            ->getJson('/api/users')
            ->assertForbidden()
            ->assertExactJson([
                'message' => 'Forbidden.',
            ]);
    }

    public function test_admin_can_filter_by_role_and_search(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        User::query()->create([
            'name' => 'Anthony Rengifo',
            'email' => 'anthony@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'Password*123',
            'role' => 'user',
        ]);

        $response = $this->actingAs($admin, 'web')
            ->getJson('/api/users?search=ANTHONY&role=admin');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.email', 'anthony@example.com')
            ->assertJsonPath('data.0.role', 'admin');
    }

    public function test_admin_gets_empty_paginated_response_when_no_results_match(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'web')
            ->getJson('/api/users?search=missing-user');

        $response
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.total', 0)
            ->assertJsonPath('meta.last_page', 1);
    }

    public function test_admin_cannot_request_invalid_role_or_per_page(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'web')
            ->getJson('/api/users?role=invalid&per_page=101');

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['role', 'per_page']);
    }
}
