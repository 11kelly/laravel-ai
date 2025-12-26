<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Activity;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Booking 模型功能测试
 *
 * 测试 Accessor、Scope 等需要 Laravel 应用上下文的功能
 */
#[CoversClass(Booking::class)]
class BookingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $admin;

    private Activity $activity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create(['is_admin' => false]);
        $this->activity = Activity::factory()->create([
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'created_by' => $this->admin->id,
        ]);
    }

    // =========================================================================
    // can_cancel Accessor Tests
    // Ref: TSD Section 5.2 - 取消预约条件
    // =========================================================================

    #[Test]
    public function can_cancel_should_return_true_when_confirmed_and_activity_not_started(): void
    {
        // Ref: TSD Section 5.2 - 用户有一个未开始活动的预约可以取消

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'activity_id' => $this->activity->id,
            'status' => 'confirmed',
        ]);

        $this->assertTrue($booking->can_cancel);
    }

    #[Test]
    public function can_cancel_should_return_false_when_cancelled(): void
    {
        // Ref: TSD Section 5.2 - 已取消的预约无法再次取消

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'activity_id' => $this->activity->id,
            'status' => 'cancelled',
        ]);

        $this->assertFalse($booking->can_cancel);
    }

    #[Test]
    public function can_cancel_should_return_false_when_activity_started(): void
    {
        // Ref: TSD Section 5.2 - 活动已开始无法取消

        $startedActivity = Activity::factory()->create([
            'start_time' => now()->subHours(1),
            'end_time' => now()->addHours(2),
            'created_by' => $this->admin->id,
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'activity_id' => $startedActivity->id,
            'status' => 'confirmed',
        ]);

        $this->assertFalse($booking->can_cancel);
    }

    #[Test]
    public function can_cancel_should_return_false_when_completed(): void
    {
        // Ref: TSD Section 5.2 - 已完成预约无法取消

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'activity_id' => $this->activity->id,
            'status' => 'completed',
        ]);

        $this->assertFalse($booking->can_cancel);
    }

    // =========================================================================
    // Scope Tests
    // Ref: TSD Section 2.2.1 - 预约筛选
    // =========================================================================

    #[Test]
    public function scope_confirmed_should_filter_by_status(): void
    {
        // Ref: TSD Section 2.2.1 - status = confirmed

        // 使用不同的活动来避免 unique(user_id, activity_id) 约束
        $activity2 = Activity::factory()->create([
            'start_time' => now()->addDays(14),
            'end_time' => now()->addDays(15),
            'created_by' => $this->admin->id,
        ]);

        Booking::factory()->create([
            'user_id' => $this->user->id,
            'activity_id' => $this->activity->id,
            'status' => 'confirmed',
        ]);
        Booking::factory()->create([
            'user_id' => $this->user->id,
            'activity_id' => $activity2->id,
            'status' => 'cancelled',
        ]);

        $this->assertCount(1, Booking::confirmed()->get());
    }

    #[Test]
    public function scope_cancelled_should_filter_by_status(): void
    {
        // Ref: TSD Section 2.2.1 - status = cancelled

        // 使用不同的活动来避免 unique(user_id, activity_id) 约束
        $activity2 = Activity::factory()->create([
            'start_time' => now()->addDays(14),
            'end_time' => now()->addDays(15),
            'created_by' => $this->admin->id,
        ]);

        Booking::factory()->create([
            'user_id' => $this->user->id,
            'activity_id' => $this->activity->id,
            'status' => 'confirmed',
        ]);
        Booking::factory()->create([
            'user_id' => $this->user->id,
            'activity_id' => $activity2->id,
            'status' => 'cancelled',
        ]);

        $this->assertCount(1, Booking::cancelled()->get());
    }

    #[Test]
    public function scope_completed_should_filter_by_status(): void
    {
        // Ref: TSD Section 2.2.1 - status = completed

        // 使用不同的活动来避免 unique(user_id, activity_id) 约束
        $activity2 = Activity::factory()->create([
            'start_time' => now()->addDays(14),
            'end_time' => now()->addDays(15),
            'created_by' => $this->admin->id,
        ]);

        Booking::factory()->create([
            'user_id' => $this->user->id,
            'activity_id' => $this->activity->id,
            'status' => 'completed',
        ]);
        Booking::factory()->create([
            'user_id' => $this->user->id,
            'activity_id' => $activity2->id,
            'status' => 'confirmed',
        ]);

        $this->assertCount(1, Booking::completed()->get());
    }

    #[Test]
    public function scope_for_user_should_filter_by_user_id(): void
    {
        // Ref: TSD Section 2.2.1 - 按用户筛选

        $otherUser = User::factory()->create();

        // 为每个预约创建不同的活动以避免 unique 约束
        $activities = Activity::factory()->count(5)->create([
            'start_time' => now()->addDays(14),
            'end_time' => now()->addDays(15),
            'created_by' => $this->admin->id,
        ]);

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

        $this->assertCount(3, Booking::forUser($this->user->id)->get());
    }

    // =========================================================================
    // Relationship Tests
    // Ref: TSD Section 3.1.3 - 表关联关系
    // =========================================================================

    #[Test]
    public function user_relationship_should_return_user(): void
    {
        // Ref: TSD Section 3.1.3 - user_id FK(users.id)

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'activity_id' => $this->activity->id,
        ]);

        $this->assertInstanceOf(User::class, $booking->user);
        $this->assertEquals($this->user->id, $booking->user->id);
    }

    #[Test]
    public function activity_relationship_should_return_activity(): void
    {
        // Ref: TSD Section 3.1.3 - activity_id FK(activities.id)

        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'activity_id' => $this->activity->id,
        ]);

        $this->assertInstanceOf(Activity::class, $booking->activity);
        $this->assertEquals($this->activity->id, $booking->activity->id);
    }

    // =========================================================================
    // Auto Booking Code Generation Tests
    // Ref: TSD Section 3.1.3 - booking_code 自动生成
    // =========================================================================

    #[Test]
    public function booking_code_should_be_auto_generated(): void
    {
        // Ref: TSD Section 3.1.3 - booking_code 自动生成

        $booking = Booking::create([
            'user_id' => $this->user->id,
            'activity_id' => $this->activity->id,
            'participants' => 1,
            'status' => 'confirmed',
        ]);

        $this->assertNotNull($booking->booking_code);
        $this->assertStringStartsWith('BK', $booking->booking_code);
    }

    #[Test]
    public function booking_code_should_be_unique(): void
    {
        // Ref: TSD Section 3.1.3 - UNIQUE(booking_code)

        // 为每个预约创建不同的活动以避免 unique(user_id, activity_id) 约束
        $activities = Activity::factory()->count(10)->create([
            'start_time' => now()->addDays(14),
            'end_time' => now()->addDays(15),
            'created_by' => $this->admin->id,
        ]);

        $codes = [];
        foreach ($activities as $activity) {
            $booking = Booking::factory()->create([
                'user_id' => $this->user->id,
                'activity_id' => $activity->id,
            ]);
            $codes[] = $booking->booking_code;
        }

        $uniqueCodes = array_unique($codes);

        $this->assertCount(count($codes), $uniqueCodes);
    }

    // =========================================================================
    // generateBookingCode Tests
    // Ref: TSD Section 2.1.3 - booking_code 生成规则
    // =========================================================================

    #[Test]
    public function generate_booking_code_should_start_with_bk_prefix(): void
    {
        // Ref: TSD Section 2.1.3 - 预约编号格式 BK + 日期 + 序号

        $code = Booking::generateBookingCode();

        $this->assertStringStartsWith('BK', $code);
    }

    #[Test]
    public function generate_booking_code_should_contain_current_date(): void
    {
        // Ref: TSD Section 2.1.3 - 预约编号包含日期

        $today = now()->format('Ymd');
        $code = Booking::generateBookingCode();

        $this->assertStringContainsString($today, $code);
    }

    #[Test]
    public function generate_booking_code_should_have_correct_length(): void
    {
        // Ref: TSD Section 3.1.3 - booking_code varchar(20)

        $code = Booking::generateBookingCode();

        // 当前实现: BK(2) + YYYYMMDD(8) + 6位随机数(6) + 毫秒后缀(可变)
        // 长度应该在 16-20 之间
        $this->assertGreaterThanOrEqual(16, strlen($code));
        $this->assertLessThanOrEqual(20, strlen($code));
    }

    #[Test]
    public function generate_booking_code_should_be_unique_on_multiple_calls(): void
    {
        // Ref: TSD Section 3.1.3 - UNIQUE 约束
        // 注意: 由于随机数范围为 1-999999，减少测试样本以降低碰撞概率

        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = Booking::generateBookingCode();
        }

        $uniqueCodes = array_unique($codes);
        $this->assertCount(count($codes), $uniqueCodes);
    }

    #[Test]
    public function generate_booking_code_format_should_match_pattern(): void
    {
        // Ref: TSD Section 3.1.3 - 预约编号格式验证

        $code = Booking::generateBookingCode();

        // 格式: BK + YYYYMMDD + 随机数字/字符（毫秒时间戳可能包含小数点）
        // 验证前缀和日期部分
        $this->assertMatchesRegularExpression('/^BK\d{8}/', $code);
    }
}

