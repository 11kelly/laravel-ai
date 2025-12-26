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
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    private BookingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BookingService();
    }

    /**
     * Test createBooking creates booking successfully.
     */
    public function test_create_booking_creates_booking_successfully(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->create(['max_participants' => 10, 'current_participants' => 0]);

        $booking = $this->service->createBooking($activity->id, $user->id, 'Test notes');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'activity_id' => $activity->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'notes' => 'Test notes',
        ]);
    }

    /**
     * Test createBooking increments current_participants when max_participants > 0.
     */
    public function test_create_booking_increments_current_participants_when_max_participants_greater_than_zero(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->create(['max_participants' => 10, 'current_participants' => 5]);

        $this->service->createBooking($activity->id, $user->id);

        $activity->refresh();
        $this->assertEquals(6, $activity->current_participants);
    }

    /**
     * Test createBooking does not increment current_participants when max_participants is 0.
     */
    public function test_create_booking_does_not_increment_when_max_participants_is_zero(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->unlimited()
            ->create(['current_participants' => 0]);

        $this->service->createBooking($activity->id, $user->id);

        $activity->refresh();
        $this->assertEquals(0, $activity->current_participants);
    }

    /**
     * Test createBooking throws exception when activity is not available.
     */
    public function test_create_booking_throws_exception_when_activity_not_available(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()
            ->create(['status' => 'draft']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Activity is not available for booking');
        $this->expectExceptionCode(409);

        $this->service->createBooking($activity->id, $user->id);
    }

    /**
     * Test createBooking throws exception when activity is full.
     */
    public function test_create_booking_throws_exception_when_activity_is_full(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->full()
            ->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Activity is not available for booking');
        $this->expectExceptionCode(409);

        $this->service->createBooking($activity->id, $user->id);
    }

    /**
     * Test createBooking throws exception when activity has started.
     */
    public function test_create_booking_throws_exception_when_activity_has_started(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()
            ->published()
            ->ongoing()
            ->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Activity is not available for booking');
        $this->expectExceptionCode(409);

        $this->service->createBooking($activity->id, $user->id);
    }

    /**
     * Test createBooking throws exception for duplicate booking.
     */
    public function test_create_booking_throws_exception_for_duplicate_booking(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->create();

        $this->service->createBooking($activity->id, $user->id);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You have already booked this activity');
        $this->expectExceptionCode(409);

        $this->service->createBooking($activity->id, $user->id);
    }

    /**
     * Test cancelBooking cancels booking successfully.
     */
    public function test_cancel_booking_cancels_booking_successfully(): void
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

        $result = $this->service->cancelBooking($booking->id, $user->id, 'Test reason');

        $this->assertEquals('cancelled', $result->status);
        $this->assertNotNull($result->cancelled_at);
        $this->assertEquals('Test reason', $result->cancelled_reason);
    }

    /**
     * Test cancelBooking decrements current_participants when max_participants > 0.
     */
    public function test_cancel_booking_decrements_current_participants_when_max_participants_greater_than_zero(): void
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

        $this->service->cancelBooking($booking->id, $user->id);

        $activity->refresh();
        $this->assertEquals(4, $activity->current_participants);
    }

    /**
     * Test cancelBooking does not decrement when max_participants is 0.
     */
    public function test_cancel_booking_does_not_decrement_when_max_participants_is_zero(): void
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

        $this->service->cancelBooking($booking->id, $user->id);

        $activity->refresh();
        $this->assertEquals(0, $activity->current_participants);
    }

    /**
     * Test cancelBooking throws exception when booking cannot be cancelled.
     */
    public function test_cancel_booking_throws_exception_when_booking_cannot_be_cancelled(): void
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

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This booking cannot be cancelled');
        $this->expectExceptionCode(409);

        $this->service->cancelBooking($booking->id, $user->id);
    }

    /**
     * Test cancelBooking throws exception for other user's booking.
     */
    public function test_cancel_booking_throws_exception_for_other_users_booking(): void
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

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->cancelBooking($booking->id, $user2->id);
    }

    /**
     * Test getUserBookings returns user's bookings.
     */
    public function test_get_user_bookings_returns_users_bookings(): void
    {
        $user = User::factory()->create();
        Booking::factory()->count(3)->create(['user_id' => $user->id]);
        Booking::factory()->count(2)->create(); // Other user's bookings

        $result = $this->service->getUserBookings($user->id);

        $this->assertCount(3, $result->items());
        $this->assertTrue($result->every(fn ($booking) => $booking->user_id === $user->id));
    }

    /**
     * Test getUserBookings filters by status.
     */
    public function test_get_user_bookings_filters_by_status(): void
    {
        $user = User::factory()->create();
        Booking::factory()->count(2)->create(['user_id' => $user->id, 'status' => 'pending']);
        Booking::factory()->count(1)->confirmed()->create(['user_id' => $user->id]);

        $result = $this->service->getUserBookings($user->id, ['status' => 'pending']);

        $this->assertCount(2, $result->items());
        $this->assertTrue($result->every(fn ($booking) => $booking->status === 'pending'));
    }

    /**
     * Test getAllBookings returns all bookings with filters.
     */
    public function test_get_all_bookings_returns_all_bookings_with_filters(): void
    {
        $activity = Activity::factory()->create();
        Booking::factory()->count(2)->create(['activity_id' => $activity->id]);
        Booking::factory()->count(1)->create();

        $result = $this->service->getAllBookings(['activity_id' => $activity->id]);

        $this->assertCount(2, $result->items());
        $this->assertTrue($result->every(fn ($booking) => $booking->activity_id === $activity->id));
    }
}

