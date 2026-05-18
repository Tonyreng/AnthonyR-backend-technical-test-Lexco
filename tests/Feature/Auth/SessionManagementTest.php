<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_fetch_current_session_user(): void
    {
        $user = User::query()->create([
            'name' => 'Anthony Rengifo',
            'email' => 'anthony@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($user, 'web')
            ->getJson('/api/auth/me');

        $response
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.role', 'admin')
            ->assertJsonMissingPath('data.user.password')
            ->assertJsonMissingPath('data.user.remember_token');
    }

    public function test_me_returns_unauthorized_for_guest_users(): void
    {
        $this->getJson('/api/auth/me')
            ->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_authenticated_user_can_logout_and_lose_session(): void
    {
        $user = User::query()->create([
            'name' => 'Anthony Rengifo',
            'email' => 'anthony@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $this->actingAs($user, 'web')
            ->postJson('/api/auth/logout')
            ->assertNoContent();

        $this->assertGuest('web');

        $this->getJson('/api/auth/me')
            ->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_logout_returns_unauthorized_for_guest_users(): void
    {
        $this->postJson('/api/auth/logout')
            ->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}
