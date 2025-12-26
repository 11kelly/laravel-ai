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
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BookingConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test concurrent booking requests for limited capacity activity.
     * This test simulates multiple users trying to book the last available spot.
     */
    public function test_concurrent_booking_prevents_overbooking(): void
    {
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->create(['max_participants' => 1, 'current_participants' => 0]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Simulate concurrent booking attempts
        $results = [];
        
        // Use database transactions to simulate real concurrency
        DB::transaction(function () use ($activity, $user1, &$results) {
            $results[] = $this->actingAs($user1)
                ->postJson("/activities/{$activity->id}/bookings");
        });

        DB::transaction(function () use ($activity, $user2, &$results) {
            $results[] = $this->actingAs($user2)
                ->postJson("/activities/{$activity->id}/bookings");
        });

        // One should succeed (201), one should fail (409)
        $successCount = 0;
        $failCount = 0;

        foreach ($results as $response) {
            if ($response->status() === 201) {
                $successCount++;
            } elseif ($response->status() === 409) {
                $failCount++;
            }
        }

        $this->assertEquals(1, $successCount, 'Exactly one booking should succeed');
        $this->assertEquals(1, $failCount, 'Exactly one booking should fail');

        // Verify activity participant count is correct
        $activity->refresh();
        $this->assertEquals(1, $activity->current_participants, 'Activity should have exactly 1 participant');

        // Verify only one booking exists
        $this->assertCount(1, Booking::where('activity_id', $activity->id)->get());
    }

    /**
     * Test that duplicate booking attempts are prevented.
     */
    public function test_duplicate_booking_attempts_are_prevented(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->create(['max_participants' => 10, 'current_participants' => 0]);

        // First booking succeeds
        $response1 = $this->actingAs($user)
            ->postJson("/activities/{$activity->id}/bookings");

        $response1->assertStatus(201);

        // Second booking attempt should fail
        $response2 = $this->actingAs($user)
            ->postJson("/activities/{$activity->id}/bookings");

        $response2->assertStatus(409)
            ->assertJson(['message' => 'You have already booked this activity']);

        // Verify only one booking exists
        $this->assertCount(1, Booking::where('activity_id', $activity->id)
            ->where('user_id', $user->id)
            ->get());
    }

    /**
     * Test that unlimited activities don't have capacity issues.
     */
    public function test_unlimited_activity_allows_multiple_bookings(): void
    {
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->unlimited()
            ->create(['current_participants' => 0]);

        $users = User::factory()->count(10)->create();

        foreach ($users as $user) {
            $response = $this->actingAs($user)
                ->postJson("/activities/{$activity->id}/bookings");

            $response->assertStatus(201);
        }

        // Verify all bookings were created
        $this->assertCount(10, Booking::where('activity_id', $activity->id)->get());

        // Verify participant count remains 0 for unlimited activities
        $activity->refresh();
        $this->assertEquals(0, $activity->current_participants);
    }

    /**
     * Test that booking cancellation properly decrements participant count.
     */
    public function test_booking_cancellation_decrements_participant_count(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->create(['max_participants' => 10, 'current_participants' => 5]);

        $booking = Booking::factory()->create([
            'activity_id' => $activity->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        // Manually increment to simulate booking creation
        $activity->increment('current_participants');
        $activity->refresh();
        $this->assertEquals(6, $activity->current_participants);

        // Cancel booking
        $response = $this->actingAs($user)
            ->patchJson("/user/bookings/{$booking->id}/cancel");

        $response->assertStatus(200);

        // Verify participant count decreased
        $activity->refresh();
        $this->assertEquals(5, $activity->current_participants);
    }

    /**
     * Test that booking full activity then cancelling allows new bookings.
     */
    public function test_cancelling_full_activity_allows_new_bookings(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->create(['max_participants' => 1, 'current_participants' => 0]);

        // User1 books
        $booking1 = $this->actingAs($user1)
            ->postJson("/activities/{$activity->id}/bookings");
        $booking1->assertStatus(201);

        // User2 tries to book - should fail
        $booking2 = $this->actingAs($user2)
            ->postJson("/activities/{$activity->id}/bookings");
        $booking2->assertStatus(409);

        // User1 cancels
        $bookingId = $booking1->json('id');
        $cancelResponse = $this->actingAs($user1)
            ->patchJson("/user/bookings/{$bookingId}/cancel");
        $cancelResponse->assertStatus(200);

        // User2 can now book
        $booking3 = $this->actingAs($user2)
            ->postJson("/activities/{$activity->id}/bookings");
        $booking3->assertStatus(201);
    }
}

