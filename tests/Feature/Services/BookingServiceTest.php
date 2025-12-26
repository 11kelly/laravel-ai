<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\Activity;
use App\Models\Booking;
use App\Models\User;
use App\Services\ActivityService;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * BookingService 功能测试
 *
 * 测试与数据库交互的预约业务逻辑
 */
#[CoversClass(BookingService::class)]
class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    private BookingService $service;

    private ActivityService $activityService;

    private User $user;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activityService = new ActivityService();
        $this->service = new BookingService($this->activityService);

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create(['is_admin' => false]);
    }

    // =========================================================================
    // createBooking Tests
    // Ref: TSD Section 2.1.3 - 预约活动接口
    // =========================================================================

    #[Test]
    public function create_booking_should_succeed_with_valid_data(): void
    {
        // Ref: TSD Section 5.1 - 用户预约活动流程

        // Arrange
        $activity = Activity::factory()->create([
            'status' => 'published',
            'capacity' => 100,
            'booked_count' => 50,
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'registration_deadline' => now()->addDays(5),
            'created_by' => $this->admin->id,
        ]);

        // Act
        $result = $this->service->createBooking($this->user, $activity, 2, '测试备注');

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('预约成功', $result['message']);
        $this->assertArrayHasKey('booking', $result);
        $this->assertEquals(2, $result['booking']->participants);
        $this->assertEquals('confirmed', $result['booking']->status);
    }

    #[Test]
    public function create_booking_should_increment_booked_count(): void
    {
        // Ref: TSD Section 5.1 - 更新活动 booked_count

        // Arrange
        $activity = Activity::factory()->create([
            'status' => 'published',
            'capacity' => 100,
            'booked_count' => 50,
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'created_by' => $this->admin->id,
        ]);

        // Act
        $this->service->createBooking($this->user, $activity, 3);
        $activity->refresh();

        // Assert
        $this->assertEquals(53, $activity->booked_count);
    }

    #[Test]
    public function create_booking_should_fail_when_activity_not_published(): void
    {
        // Ref: TSD Section 5.1 - 活动未发布

        // Arrange
        $activity = Activity::factory()->create([
            'status' => 'draft',
            'capacity' => 100,
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'created_by' => $this->admin->id,
        ]);

        // Act
        $result = $this->service->createBooking($this->user, $activity);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('活动未发布', $result['message']);
        $this->assertEquals(409, $result['code']);
    }

    #[Test]
    public function create_booking_should_fail_when_activity_ended(): void
    {
        // Ref: TSD Section 5.1 - 活动已结束

        // Arrange
        $activity = Activity::factory()->create([
            'status' => 'published',
            'capacity' => 100,
            'start_time' => now()->subDays(2),
            'end_time' => now()->subDays(1),
            'created_by' => $this->admin->id,
        ]);

        // Act
        $result = $this->service->createBooking($this->user, $activity);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('活动已结束', $result['message']);
    }

    #[Test]
    public function create_booking_should_fail_when_registration_deadline_passed(): void
    {
        // Ref: TSD Section 5.1 - 已过报名截止时间

        // Arrange
        $activity = Activity::factory()->create([
            'status' => 'published',
            'capacity' => 100,
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'registration_deadline' => now()->subHours(1),
            'created_by' => $this->admin->id,
        ]);

        // Act
        $result = $this->service->createBooking($this->user, $activity);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('报名已截止', $result['message']);
    }

    #[Test]
    public function create_booking_should_fail_when_capacity_exceeded(): void
    {
        // Ref: TSD Section 5.1 - 名额已满

        // Arrange
        $activity = Activity::factory()->create([
            'status' => 'published',
            'capacity' => 100,
            'booked_count' => 98,
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'created_by' => $this->admin->id,
        ]);

        // Act
        $result = $this->service->createBooking($this->user, $activity, 5);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('名额不足', $result['message']);
    }

    #[Test]
    public function create_booking_should_fail_when_user_already_booked(): void
    {
        // Ref: TSD Section 5.1 - 用户已预约过

        // Arrange
        $activity = Activity::factory()->create([
            'status' => 'published',
            'capacity' => 100,
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'created_by' => $this->admin->id,
        ]);

        // 先创建一个预约
        Booking::factory()->create([
            'user_id' => $this->user->id,
            'activity_id' => $activity->id,
            'status' => 'confirmed',
        ]);

        // Act
        $result = $this->service->createBooking($this->user, $activity);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('您已预约过此活动', $result['message']);
    }

    #[Test]
    public function create_booking_should_generate_unique_booking_code(): void
    {
        // Ref: TSD Section 3.1.3 - UNIQUE(booking_code)

        // Arrange
        $activity = Activity::factory()->create([
            'status' => 'published',
            'capacity' => 100,
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'created_by' => $this->admin->id,
        ]);

        // Act
        $result = $this->service->createBooking($this->user, $activity);

        // Assert
        $this->assertNotNull($result['booking']->booking_code);
        $this->assertStringStartsWith('BK', $result['booking']->booking_code);
    }

    // =========================================================================
    // cancelBooking Tests
    // Ref: TSD Section 2.1.4 - 取消预约接口
    // =========================================================================

    #[Test]
    public function cancel_booking_should_succeed_for_confirmed_booking(): void
    {
        // Ref: TSD Section 5.2 - 用户取消预约流程

        // Arrange
        $activity = Activity::factory()->create([
            'status' => 'published',
            'capacity' => 100,
            'booked_count' => 10,
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'created_by' => $this->admin->id,
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'activity_id' => $activity->id,
            'status' => 'confirmed',
            'participants' => 2,
        ]);

        // Act
        $result = $this->service->cancelBooking($booking, '有事无法参加');

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('预约已取消', $result['message']);
    }

    #[Test]
    public function cancel_booking_should_update_booking_status(): void
    {
        // Ref: TSD Section 5.2 - 更新预约状态为 cancelled

        // Arrange
        $activity = Activity::factory()->create([
            'status' => 'published',
            'booked_count' => 10,
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'created_by' => $this->admin->id,
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'activity_id' => $activity->id,
            'status' => 'confirmed',
            'participants' => 2,
        ]);

        // Act
        $this->service->cancelBooking($booking, '有事无法参加');
        $booking->refresh();

        // Assert
        $this->assertEquals('cancelled', $booking->status);
        $this->assertEquals('有事无法参加', $booking->cancellation_reason);
        $this->assertNotNull($booking->cancelled_at);
    }

    #[Test]
    public function cancel_booking_should_decrement_booked_count(): void
    {
        // Ref: TSD Section 5.2 - 减少活动 booked_count

        // Arrange
        $activity = Activity::factory()->create([
            'status' => 'published',
            'booked_count' => 10,
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'created_by' => $this->admin->id,
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'activity_id' => $activity->id,
            'status' => 'confirmed',
            'participants' => 3,
        ]);

        // Act
        $this->service->cancelBooking($booking);
        $activity->refresh();

        // Assert
        $this->assertEquals(7, $activity->booked_count);
    }

    #[Test]
    public function cancel_booking_should_fail_when_not_confirmed(): void
    {
        // Ref: TSD Section 5.2 - 非 confirmed 状态

        // Arrange
        $activity = Activity::factory()->create([
            'status' => 'published',
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'created_by' => $this->admin->id,
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'activity_id' => $activity->id,
            'status' => 'cancelled',
        ]);

        // Act
        $result = $this->service->cancelBooking($booking);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('此预约无法取消', $result['message']);
    }

    #[Test]
    public function cancel_booking_should_fail_when_activity_started(): void
    {
        // Ref: TSD Section 5.2 - 活动已开始无法取消

        // Arrange
        $activity = Activity::factory()->create([
            'status' => 'published',
            'start_time' => now()->subHours(1),
            'end_time' => now()->addHours(2),
            'created_by' => $this->admin->id,
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'activity_id' => $activity->id,
            'status' => 'confirmed',
        ]);

        // Act
        $result = $this->service->cancelBooking($booking);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('活动已开始，无法取消', $result['message']);
    }

    // =========================================================================
    // getUserBookings Tests
    // Ref: TSD Section 2.2.1 - 获取我的预约列表
    // =========================================================================

    #[Test]
    public function get_user_bookings_should_return_user_bookings_only(): void
    {
        // Ref: TSD Section 2.2.1 - 仅返回当前用户预约

        // Arrange
        // 为每个预约创建不同的活动以避免 unique(user_id, activity_id) 约束
        $activities = Activity::factory()->count(5)->create([
            'created_by' => $this->admin->id,
        ]);

        $otherUser = User::factory()->create();

        // 为 user 创建 3 个预约
        foreach ($activities->take(3) as $activity) {
            Booking::factory()->create([
                'user_id' => $this->user->id,
                'activity_id' => $activity->id,
            ]);
        }

        // 为 otherUser 创建 2 个预约
        foreach ($activities->skip(3)->take(2) as $activity) {
            Booking::factory()->create([
                'user_id' => $otherUser->id,
                'activity_id' => $activity->id,
            ]);
        }

        // Act
        $result = $this->service->getUserBookings($this->user);

        // Assert
        $this->assertEquals(3, $result->total());
    }

    #[Test]
    public function get_user_bookings_should_filter_by_status(): void
    {
        // Ref: TSD Section 2.2.1 - status 筛选

        // Arrange
        // 为每个预约创建不同的活动以避免 unique(user_id, activity_id) 约束
        $activities = Activity::factory()->count(3)->create([
            'created_by' => $this->admin->id,
        ]);

        // 创建 2 个 confirmed 预约
        foreach ($activities->take(2) as $activity) {
            Booking::factory()->create([
                'user_id' => $this->user->id,
                'activity_id' => $activity->id,
                'status' => 'confirmed',
            ]);
        }

        // 创建 1 个 cancelled 预约
        Booking::factory()->create([
            'user_id' => $this->user->id,
            'activity_id' => $activities->last()->id,
            'status' => 'cancelled',
        ]);

        // Act
        $result = $this->service->getUserBookings($this->user, 'cancelled');

        // Assert
        $this->assertEquals(1, $result->total());
    }

    // =========================================================================
    // hasUserBooked Tests
    // Ref: TSD Section 6.2 - 幂等性与去重
    // =========================================================================

    #[Test]
    public function has_user_booked_should_return_true_when_booked(): void
    {
        // Ref: TSD Section 6.2 - 重复预约检查

        // Arrange
        $activity = Activity::factory()->create([
            'created_by' => $this->admin->id,
        ]);

        Booking::factory()->create([
            'user_id' => $this->user->id,
            'activity_id' => $activity->id,
            'status' => 'confirmed',
        ]);

        // Act
        $result = $this->service->hasUserBooked($this->user, $activity);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function has_user_booked_should_return_false_when_cancelled(): void
    {
        // Ref: TSD Section 6.2 - 已取消的预约可以重新预约

        // Arrange
        $activity = Activity::factory()->create([
            'created_by' => $this->admin->id,
        ]);

        Booking::factory()->create([
            'user_id' => $this->user->id,
            'activity_id' => $activity->id,
            'status' => 'cancelled',
        ]);

        // Act
        $result = $this->service->hasUserBooked($this->user, $activity);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function has_user_booked_should_return_false_when_not_booked(): void
    {
        // Ref: TSD Section 6.2 - 未预约

        // Arrange
        $activity = Activity::factory()->create([
            'created_by' => $this->admin->id,
        ]);

        // Act
        $result = $this->service->hasUserBooked($this->user, $activity);

        // Assert
        $this->assertFalse($result);
    }
}

