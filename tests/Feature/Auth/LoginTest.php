<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receive_role_in_response(): void
    {
        $user = User::query()->create([
            'name' => 'Anthony Rengifo',
            'email' => 'anthony@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'anthony@example.com',
            'password' => 'Password*123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.role', 'admin')
            ->assertJsonMissingPath('data.user.password')
            ->assertJsonMissingPath('data.user.remember_token');

        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_login_returns_unauthorized_for_invalid_credentials(): void
    {
        User::query()->create([
            'name' => 'Anthony Rengifo',
            'email' => 'anthony@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'anthony@example.com',
            'password' => 'WrongPassword*123',
        ]);

        $response
            ->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Invalid credentials',
            ]);

        $this->assertGuest('web');
    }

    public function test_login_validates_required_fields_and_email_format(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'invalid-email',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }
}
