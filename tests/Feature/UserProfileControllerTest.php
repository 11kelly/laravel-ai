<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test show returns user profile.
     */
    public function test_show_returns_user_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '1234567890',
        ]);

        $response = $this->actingAs($user)
            ->get('/user/profile');

        $response->assertStatus(200);
        $response->assertViewIs('user.profile.show');
        $response->assertViewHas('user');
        $this->assertEquals($user->id, $response->viewData('user')->id);
    }

    /**
     * Test show returns JSON when expects JSON.
     */
    public function test_show_returns_json_when_expects_json(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '1234567890',
        ]);

        $response = $this->actingAs($user)
            ->getJson('/user/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'phone',
                'avatar_path',
                'created_at',
            ])
            ->assertJson([
                'id' => $user->id,
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '1234567890',
            ]);
    }

    /**
     * Test show requires authentication.
     */
    public function test_show_requires_authentication(): void
    {
        $response = $this->get('/user/profile');

        $response->assertRedirect(route('login'));
    }

    /**
     * Test update updates user profile successfully.
     */
    public function test_update_updates_user_profile_successfully(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $response = $this->actingAs($user)
            ->patchJson('/user/profile', [
                'name' => 'New Name',
                'email' => 'new@example.com',
                'phone' => '1234567890',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'phone',
            ])
            ->assertJson([
                'name' => 'New Name',
                'email' => 'new@example.com',
                'phone' => '1234567890',
            ]);

        $user->refresh();
        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('new@example.com', $user->email);
        $this->assertEquals('1234567890', $user->phone);
    }

    /**
     * Test update validates name length.
     */
    public function test_update_validates_name_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->patchJson('/user/profile', [
                'name' => 'A', // Too short
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test update validates email format.
     */
    public function test_update_validates_email_format(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->patchJson('/user/profile', [
                'email' => 'invalid-email',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test update validates unique email.
     */
    public function test_update_validates_unique_email(): void
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $response = $this->actingAs($user2)
            ->patchJson('/user/profile', [
                'email' => 'user1@example.com',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test update allows same email for same user.
     */
    public function test_update_allows_same_email_for_same_user(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->actingAs($user)
            ->patchJson('/user/profile', [
                'name' => 'Updated Name',
                'email' => 'test@example.com', // Same email
            ]);

        $response->assertStatus(200);
    }

    /**
     * Test update validates phone length.
     */
    public function test_update_validates_phone_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->patchJson('/user/profile', [
                'phone' => str_repeat('1', 21), // Exceeds max 20
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test update allows partial updates.
     */
    public function test_update_allows_partial_updates(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $response = $this->actingAs($user)
            ->patchJson('/user/profile', [
                'name' => 'New Name',
                // Email not provided
            ]);

        $response->assertStatus(200);
        $user->refresh();
        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('old@example.com', $user->email); // Unchanged
    }

    /**
     * Test update requires authentication.
     */
    public function test_update_requires_authentication(): void
    {
        $response = $this->patchJson('/user/profile', [
            'name' => 'New Name',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test update returns redirect when not JSON.
     */
    public function test_update_returns_redirect_when_not_json(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->patch('/user/profile', [
                'name' => 'New Name',
            ]);

        $response->assertRedirect(route('user.profile.show'));
        $response->assertSessionHas('success');
    }
}

