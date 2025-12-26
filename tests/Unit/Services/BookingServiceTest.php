<?php

declare(strict_types=1);

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

namespace Tests\Unit\Services;

use App\Models\Activity;
use App\Models\Booking;
use App\Models\User;
use App\Services\BookingService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    private BookingService $sut;

    private User $user;

    private Activity $activity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->activity = Activity::factory()->create([
            'capacity' => 10,
            'remaining_spots' => 10,
            'status' => 'published',
        ]);

        $this->sut = new BookingService();
    }

    public function test_it_should_create_a_booking_successfully_when_inventory_is_available()
    {
        $data = [
            'ticket_quantity' => 1,
            'participant_info' => [
                'name' => 'John Doe',
                'phone' => '123456789'
            ]
        ];

        $result = $this->sut->createBooking($this->user, $this->activity, $data);

        $this->assertInstanceOf(Booking::class, $result);
        $this->assertEquals('pending', $result->status);
        $this->assertNotNull($result->booking_code);
        
        // 验证数据库
        $this->activity->refresh();
        $this->assertEquals(9, $this->activity->remaining_spots);
    }

    public function test_it_should_throw_exception_when_remaining_spots_are_not_enough()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('RESOURCE_EXHAUSTED');

        $data = [
            'ticket_quantity' => 11, // 超过容量
            'participant_info' => ['name' => 'John', 'phone' => '123']
        ];

        $this->sut->createBooking($this->user, $this->activity, $data);
    }

    public function test_it_should_cancel_a_booking_and_restore_inventory()
    {
        // 1. 先创建一个预约
        $booking = Booking::factory()->create([
            'activity_id' => $this->activity->id,
            'user_id' => $this->user->id,
            'ticket_quantity' => 2,
            'status' => 'pending',
        ]);
        
        $initialSpots = $this->activity->remaining_spots; // 假设此时是10

        // 2. 取消预约
        $this->sut->cancelBooking($booking);

        // 3. 验证
        $this->assertEquals('cancelled', $booking->fresh()->status);
        $this->assertEquals($initialSpots + 2, $this->activity->fresh()->remaining_spots);
    }

    public function test_it_should_throw_exception_when_cancelling_already_cancelled_booking()
    {
        $booking = Booking::factory()->create(['status' => 'cancelled']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('BOOKING_ALREADY_CANCELLED');

        $this->sut->cancelBooking($booking);
    }
}

