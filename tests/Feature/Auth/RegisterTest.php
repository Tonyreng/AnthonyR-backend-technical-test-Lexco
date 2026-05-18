<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_registered_user_receives_admin_role_and_is_authenticated(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Anthony Rengifo',
            'email' => 'anthony@example.com',
            'password' => 'Password*123',
            'password_confirmation' => 'Password*123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.user.email', 'anthony@example.com')
            ->assertJsonPath('data.user.role', 'admin')
            ->assertJsonMissingPath('data.user.password')
            ->assertJsonMissingPath('data.user.remember_token');

        $this->assertAuthenticated('web');
        $this->assertDatabaseHas('users', [
            'email' => 'anthony@example.com',
            'role' => 'admin',
        ]);
    }

    public function test_subsequent_registered_users_receive_user_role(): void
    {
        User::query()->create([
            'name' => 'Existing Admin',
            'email' => 'admin@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'Password*123',
            'password_confirmation' => 'Password*123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.user.role', 'user');

        $this->assertDatabaseHas('users', [
            'email' => 'user@example.com',
            'role' => 'user',
        ]);
    }

    public function test_register_ignores_role_sent_by_client(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Anthony Rengifo',
            'email' => 'anthony@example.com',
            'password' => 'Password*123',
            'password_confirmation' => 'Password*123',
            'role' => 'user',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.user.role', 'admin');
    }

    public function test_register_validates_unique_email_and_password_requirements(): void
    {
        User::query()->create([
            'name' => 'Existing User',
            'email' => 'anthony@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Anthony Rengifo',
            'email' => 'anthony@example.com',
            'password' => 'weakpass',
            'password_confirmation' => 'weakpass',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }
}
