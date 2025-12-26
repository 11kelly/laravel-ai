<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test store creates booking successfully.
     */
    public function test_store_creates_booking_successfully(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->create(['max_participants' => 10, 'current_participants' => 0]);

        $response = $this->actingAs($user)
            ->postJson("/activities/{$activity->id}/bookings", [
                'notes' => 'Test booking notes',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'activity_id',
                'user_id',
                'status',
                'created_at',
            ])
            ->assertJson([
                'activity_id' => $activity->id,
                'user_id' => $user->id,
                'status' => 'pending',
            ]);

        $this->assertDatabaseHas('bookings', [
            'activity_id' => $activity->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'notes' => 'Test booking notes',
        ]);
    }

    /**
     * Test store returns 401 when user is not authenticated.
     */
    public function test_store_returns_401_when_user_not_authenticated(): void
    {
        $activity = Activity::factory()->published()->upcoming()->create();

        $response = $this->postJson("/activities/{$activity->id}/bookings");

        // Laravel returns 401 for unauthenticated requests via middleware
        $response->assertStatus(401);
    }

    /**
     * Test store returns 409 when activity is not available.
     */
    public function test_store_returns_409_when_activity_not_available(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()->create(['status' => 'draft']);

        $response = $this->actingAs($user)
            ->postJson("/activities/{$activity->id}/bookings");

        $response->assertStatus(409)
            ->assertJson(['message' => 'Activity is not available for booking']);
    }

    /**
     * Test store returns 409 when activity is full.
     */
    public function test_store_returns_409_when_activity_is_full(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->full()
            ->create();

        $response = $this->actingAs($user)
            ->postJson("/activities/{$activity->id}/bookings");

        $response->assertStatus(409)
            ->assertJson(['message' => 'Activity is not available for booking']);
    }

    /**
     * Test store returns 409 when activity has started.
     */
    public function test_store_returns_409_when_activity_has_started(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()
            ->published()
            ->ongoing()
            ->create();

        $response = $this->actingAs($user)
            ->postJson("/activities/{$activity->id}/bookings");

        $response->assertStatus(409)
            ->assertJson(['message' => 'Activity is not available for booking']);
    }

    /**
     * Test store returns 409 for duplicate booking.
     */
    public function test_store_returns_409_for_duplicate_booking(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->create();

        $this->actingAs($user)
            ->postJson("/activities/{$activity->id}/bookings");

        $response = $this->actingAs($user)
            ->postJson("/activities/{$activity->id}/bookings");

        $response->assertStatus(409)
            ->assertJson(['message' => 'You have already booked this activity']);
    }

    /**
     * Test store validates notes length.
     */
    public function test_store_validates_notes_length(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->create();

        $response = $this->actingAs($user)
            ->postJson("/activities/{$activity->id}/bookings", [
                'notes' => str_repeat('a', 501), // Exceeds max 500
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test store increments current_participants when max_participants > 0.
     */
    public function test_store_increments_current_participants_when_max_participants_greater_than_zero(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->create(['max_participants' => 10, 'current_participants' => 5]);

        $this->actingAs($user)
            ->postJson("/activities/{$activity->id}/bookings");

        $activity->refresh();
        $this->assertEquals(6, $activity->current_participants);
    }

    /**
     * Test store does not increment when max_participants is 0.
     */
    public function test_store_does_not_increment_when_max_participants_is_zero(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->unlimited()
            ->create(['current_participants' => 0]);

        $this->actingAs($user)
            ->postJson("/activities/{$activity->id}/bookings");

        $activity->refresh();
        $this->assertEquals(0, $activity->current_participants);
    }

    /**
     * Test store handles concurrent booking requests correctly.
     */
    public function test_store_handles_concurrent_booking_requests_correctly(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->create(['max_participants' => 1, 'current_participants' => 0]);

        // Simulate concurrent requests
        $response1 = $this->actingAs($user1)
            ->postJson("/activities/{$activity->id}/bookings");
        $response2 = $this->actingAs($user2)
            ->postJson("/activities/{$activity->id}/bookings");

        // One should succeed, one should fail
        $successCount = 0;
        $failCount = 0;

        if ($response1->status() === 201) {
            $successCount++;
        } else {
            $failCount++;
        }

        if ($response2->status() === 201) {
            $successCount++;
        } else {
            $failCount++;
        }

        $this->assertEquals(1, $successCount);
        $this->assertEquals(1, $failCount);

        $activity->refresh();
        $this->assertEquals(1, $activity->current_participants);
    }
}

