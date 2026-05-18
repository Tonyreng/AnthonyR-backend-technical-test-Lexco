<?php

namespace Tests\Feature\User;

use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ManageUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_user(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'web')->postJson('/api/users', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'Password*123',
            'password_confirmation' => 'Password*123',
            'role' => 'user',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Jane Doe')
            ->assertJsonPath('data.email', 'jane@example.com')
            ->assertJsonPath('data.role', 'user')
            ->assertJsonPath('message', 'User created successfully')
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.remember_token');

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'role' => 'user',
        ]);
    }

    public function test_admin_can_update_a_user_without_changing_password(): void
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

        $currentPasswordHash = $user->password;

        $response = $this->actingAs($admin, 'web')->putJson("/api/users/{$user->id}", [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'role' => 'admin',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated User')
            ->assertJsonPath('data.email', 'updated@example.com')
            ->assertJsonPath('data.role', 'admin')
            ->assertJsonPath('message', 'User updated successfully');

        $user->refresh();

        $this->assertSame($currentPasswordHash, $user->password);
    }

    public function test_admin_can_update_a_user_password(): void
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

        $response = $this->actingAs($admin, 'web')->putJson("/api/users/{$user->id}", [
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'role' => 'user',
            'password' => 'NewPassword*123',
            'password_confirmation' => 'NewPassword*123',
        ]);

        $response->assertOk();

        $user->refresh();

        $this->assertTrue(Hash::check('NewPassword*123', $user->password));
    }

    public function test_guest_cannot_create_or_update_users(): void
    {
        $user = User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'Password*123',
            'role' => 'user',
        ]);

        $this->postJson('/api/users', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'Password*123',
            'password_confirmation' => 'Password*123',
            'role' => 'user',
        ])->assertUnauthorized();

        $this->putJson("/api/users/{$user->id}", [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'role' => 'user',
        ])->assertUnauthorized();

        $this->deleteJson("/api/users/{$user->id}")->assertUnauthorized();
    }

    public function test_non_admin_cannot_create_or_update_users(): void
    {
        $regularUser = User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'Password*123',
            'role' => 'user',
        ]);

        $targetUser = User::query()->create([
            'name' => 'Another User',
            'email' => 'another@example.com',
            'password' => 'Password*123',
            'role' => 'user',
        ]);

        $this->actingAs($regularUser, 'web')
            ->postJson('/api/users', [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'password' => 'Password*123',
                'password_confirmation' => 'Password*123',
                'role' => 'user',
            ])
            ->assertForbidden()
            ->assertExactJson([
                'message' => 'Forbidden.',
            ]);

        $this->actingAs($regularUser, 'web')
            ->putJson("/api/users/{$targetUser->id}", [
                'name' => 'Updated User',
                'email' => 'updated@example.com',
                'role' => 'user',
            ])
            ->assertForbidden()
            ->assertExactJson([
                'message' => 'Forbidden.',
            ]);

        $this->actingAs($regularUser, 'web')
            ->deleteJson("/api/users/{$targetUser->id}")
            ->assertForbidden()
            ->assertExactJson([
                'message' => 'Forbidden.',
            ]);
    }

    public function test_admin_cannot_create_or_update_user_with_invalid_data(): void
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

        $this->actingAs($admin, 'web')
            ->postJson('/api/users', [
                'name' => 'Jane Doe',
                'email' => 'user@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'manager',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password', 'role']);

        $this->actingAs($admin, 'web')
            ->putJson("/api/users/{$user->id}", [
                'name' => 'Updated User',
                'email' => 'admin@example.com',
                'password' => 'short',
                'password_confirmation' => 'different',
                'role' => 'manager',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password', 'role']);
    }

    public function test_admin_gets_not_found_when_updating_missing_user(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $this->actingAs($admin, 'web')
            ->putJson('/api/users/999999', [
                'name' => 'Updated User',
                'email' => 'updated@example.com',
                'role' => 'user',
            ])
            ->assertNotFound()
            ->assertExactJson([
                'message' => 'User not found',
            ]);
    }

    public function test_admin_cannot_change_own_role_to_user(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $this->actingAs($admin, 'web')
            ->putJson("/api/users/{$admin->id}", [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'role' => 'user',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['role'])
            ->assertJsonPath('errors.role.0', 'You cannot change your own admin role.');

        $admin->refresh();

        $this->assertSame('admin', $admin->role);
    }

    public function test_admin_can_delete_a_user_without_history(): void
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

        $this->actingAs($admin, 'web')
            ->deleteJson("/api/users/{$user->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_admin_gets_not_found_when_deleting_missing_user(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $this->actingAs($admin, 'web')
            ->deleteJson('/api/users/999999')
            ->assertNotFound()
            ->assertExactJson([
                'message' => 'User not found',
            ]);
    }

    public function test_admin_cannot_delete_own_user_account(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password*123',
            'role' => 'admin',
        ]);

        $this->actingAs($admin, 'web')
            ->deleteJson("/api/users/{$admin->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['user'])
            ->assertJsonPath('errors.user.0', 'You cannot delete your own user account.');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }

    public function test_admin_cannot_delete_user_with_purchase_history(): void
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

        Purchase::query()->create([
            'user_id' => $user->id,
            'total' => 100,
            'status' => 'completed',
        ]);

        $this->actingAs($admin, 'web')
            ->deleteJson("/api/users/{$user->id}")
            ->assertStatus(409)
            ->assertExactJson([
                'message' => 'User cannot be deleted because it has associated history.',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
    }
}
