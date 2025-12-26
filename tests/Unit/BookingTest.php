<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Activity;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test canBeCancelled returns true for pending booking.
     */
    public function test_can_be_cancelled_returns_true_for_pending_booking(): void
    {
        $booking = Booking::factory()->create(['status' => 'pending']);

        $this->assertTrue($booking->canBeCancelled());
    }

    /**
     * Test canBeCancelled returns false for already cancelled booking.
     */
    public function test_can_be_cancelled_returns_false_for_cancelled_booking(): void
    {
        $booking = Booking::factory()->cancelled()->create();

        $this->assertFalse($booking->canBeCancelled());
    }

    /**
     * Test canBeCancelled returns false for confirmed booking of started activity.
     */
    public function test_can_be_cancelled_returns_false_for_confirmed_booking_of_started_activity(): void
    {
        $activity = Activity::factory()->published()->ongoing()->create();
        $booking = Booking::factory()
            ->confirmed()
            ->create(['activity_id' => $activity->id]);

        $this->assertFalse($booking->canBeCancelled());
    }

    /**
     * Test canBeCancelled returns true for confirmed booking of upcoming activity.
     */
    public function test_can_be_cancelled_returns_true_for_confirmed_booking_of_upcoming_activity(): void
    {
        $activity = Activity::factory()->published()->upcoming()->create();
        $booking = Booking::factory()
            ->confirmed()
            ->create(['activity_id' => $activity->id]);

        $this->assertTrue($booking->canBeCancelled());
    }

    /**
     * Test activity relationship.
     */
    public function test_activity_relationship(): void
    {
        $activity = Activity::factory()->create();
        $booking = Booking::factory()->create(['activity_id' => $activity->id]);

        $this->assertInstanceOf(Activity::class, $booking->activity);
        $this->assertEquals($activity->id, $booking->activity->id);
    }

    /**
     * Test user relationship.
     */
    public function test_user_relationship(): void
    {
        $user = User::factory()->create();
        $booking = Booking::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $booking->user);
        $this->assertEquals($user->id, $booking->user->id);
    }

    /**
     * Test status scope filters by status.
     */
    public function test_status_scope_filters_by_status(): void
    {
        Booking::factory()->count(2)->create(['status' => 'pending']);
        Booking::factory()->count(1)->confirmed()->create();
        Booking::factory()->count(1)->cancelled()->create();

        $pending = Booking::status('pending')->get();
        $this->assertCount(2, $pending);

        $confirmed = Booking::status('confirmed')->get();
        $this->assertCount(1, $confirmed);

        $cancelled = Booking::status('cancelled')->get();
        $this->assertCount(1, $cancelled);
    }
}

