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

class UserBookingControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test index returns user's bookings.
     */
    public function test_index_returns_users_bookings(): void
    {
        $user = User::factory()->create();
        Booking::factory()->count(3)->create(['user_id' => $user->id]);
        Booking::factory()->count(2)->create(); // Other user's bookings

        $response = $this->actingAs($user)
            ->get('/user/bookings');

        $response->assertStatus(200);
        $response->assertViewIs('user.dashboard');
        $response->assertViewHas('bookings');
        $this->assertCount(3, $response->viewData('bookings')->items());
    }

    /**
     * Test index returns JSON when expects JSON.
     */
    public function test_index_returns_json_when_expects_json(): void
    {
        $user = User::factory()->create();
        Booking::factory()->count(2)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->getJson('/user/bookings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ]);
        $this->assertCount(2, $response->json('data'));
    }

    /**
     * Test index filters by status.
     */
    public function test_index_filters_by_status(): void
    {
        $user = User::factory()->create();
        Booking::factory()->count(2)->create(['user_id' => $user->id, 'status' => 'pending']);
        Booking::factory()->count(1)->confirmed()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->getJson('/user/bookings?status=pending');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        $this->assertTrue(collect($response->json('data'))->every(fn ($booking) => $booking['status'] === 'pending'));
    }

    /**
     * Test index requires authentication.
     */
    public function test_index_requires_authentication(): void
    {
        $response = $this->get('/user/bookings');

        $response->assertRedirect(route('login'));
    }

    /**
     * Test cancel cancels booking successfully.
     */
    public function test_cancel_cancels_booking_successfully(): void
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

        $response = $this->actingAs($user)
            ->patchJson("/user/bookings/{$booking->id}/cancel", [
                'reason' => 'Test cancellation reason',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'status',
                'cancelled_at',
            ])
            ->assertJson([
                'status' => 'cancelled',
            ]);

        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);
        $this->assertNotNull($booking->cancelled_at);
    }

    /**
     * Test cancel decrements current_participants when max_participants > 0.
     */
    public function test_cancel_decrements_current_participants_when_max_participants_greater_than_zero(): void
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

        $this->actingAs($user)
            ->patchJson("/user/bookings/{$booking->id}/cancel");

        $activity->refresh();
        $this->assertEquals(4, $activity->current_participants);
    }

    /**
     * Test cancel does not decrement when max_participants is 0.
     */
    public function test_cancel_does_not_decrement_when_max_participants_is_zero(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->unlimited()
            ->create(['current_participants' => 0]);

        $booking = Booking::factory()->create([
            'activity_id' => $activity->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->patchJson("/user/bookings/{$booking->id}/cancel");

        $activity->refresh();
        $this->assertEquals(0, $activity->current_participants);
    }

    /**
     * Test cancel returns 409 when booking cannot be cancelled.
     */
    public function test_cancel_returns_409_when_booking_cannot_be_cancelled(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()
            ->published()
            ->ongoing()
            ->create();

        $booking = Booking::factory()->create([
            'activity_id' => $activity->id,
            'user_id' => $user->id,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($user)
            ->patchJson("/user/bookings/{$booking->id}/cancel");

        $response->assertStatus(409)
            ->assertJson(['message' => 'This booking cannot be cancelled']);
    }

    /**
     * Test cancel returns 404 for other user's booking.
     */
    public function test_cancel_returns_404_for_other_users_booking(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->create();

        $booking = Booking::factory()->create([
            'activity_id' => $activity->id,
            'user_id' => $user1->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user2)
            ->patchJson("/user/bookings/{$booking->id}/cancel");

        $response->assertStatus(404);
    }

    /**
     * Test cancel requires authentication.
     */
    public function test_cancel_requires_authentication(): void
    {
        $booking = Booking::factory()->create();

        $response = $this->patchJson("/user/bookings/{$booking->id}/cancel");

        $response->assertStatus(401);
    }

    /**
     * Test cancel validates reason length.
     */
    public function test_cancel_validates_reason_length(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->create();

        $booking = Booking::factory()->create([
            'activity_id' => $activity->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)
            ->patchJson("/user/bookings/{$booking->id}/cancel", [
                'reason' => str_repeat('a', 501), // Exceeds max 500
            ]);

        $response->assertStatus(422);
    }
}

