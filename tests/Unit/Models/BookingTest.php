<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Booking;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Booking 模型单元测试
 *
 * 由于 Eloquent 模型依赖 Laravel 应用上下文，
 * 此测试类仅验证类结构、属性定义和纯逻辑方法。
 * 完整的 Accessor/Scope 测试请参见 Feature Tests。
 */
#[CoversClass(Booking::class)]
class BookingTest extends TestCase
{
    // =========================================================================
    // Model Structure Tests
    // Ref: TSD Section 3.1.3 - bookings 表结构
    // =========================================================================

    #[Test]
    public function model_should_have_fillable_attributes(): void
    {
        // Ref: TSD Section 3.1.3 - 预约表字段定义

        $booking = new Booking();
        $fillable = $booking->getFillable();

        // 验证关键字段存在
        $this->assertContains('booking_code', $fillable);
        $this->assertContains('user_id', $fillable);
        $this->assertContains('activity_id', $fillable);
        $this->assertContains('participants', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('remarks', $fillable);
        $this->assertContains('cancelled_at', $fillable);
        $this->assertContains('cancellation_reason', $fillable);
    }

    #[Test]
    public function model_should_have_casts_defined(): void
    {
        // Ref: TSD Section 3.1.3 - 字段类型转换

        $booking = new Booking();
        $casts = $booking->getCasts();

        $this->assertEquals('integer', $casts['participants']);
        $this->assertEquals('datetime', $casts['cancelled_at']);
    }

    // =========================================================================
    // generateBookingCode Tests
    // Note: 由于 generateBookingCode 需要 Laravel 应用上下文（使用 now()），
    // 相关测试已移至 Feature Tests: Tests\Feature\Models\BookingTest
    // =========================================================================

    #[Test]
    public function generate_booking_code_method_should_exist(): void
    {
        // Ref: TSD Section 2.1.3 - 预约编号生成方法
        $reflection = new ReflectionClass(Booking::class);

        $this->assertTrue($reflection->hasMethod('generateBookingCode'));
        $method = $reflection->getMethod('generateBookingCode');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    // =========================================================================
    // Relationship Method Tests
    // Ref: TSD Section 3.1.3 - 表关联关系
    // =========================================================================

    #[Test]
    public function model_should_have_user_relationship(): void
    {
        // Ref: TSD Section 3.1.3 - user_id FK(users.id)

        $reflection = new ReflectionClass(Booking::class);

        $this->assertTrue($reflection->hasMethod('user'));
        $method = $reflection->getMethod('user');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function model_should_have_activity_relationship(): void
    {
        // Ref: TSD Section 3.1.3 - activity_id FK(activities.id)

        $reflection = new ReflectionClass(Booking::class);

        $this->assertTrue($reflection->hasMethod('activity'));
        $method = $reflection->getMethod('activity');
        $this->assertTrue($method->isPublic());
    }

    // =========================================================================
    // Scope Method Tests
    // Ref: TSD Section 2.2.1 - 预约状态筛选
    // =========================================================================

    #[Test]
    public function model_should_have_confirmed_scope(): void
    {
        // Ref: TSD Section 2.2.1 - status = confirmed

        $reflection = new ReflectionClass(Booking::class);

        $this->assertTrue($reflection->hasMethod('scopeConfirmed'));
    }

    #[Test]
    public function model_should_have_cancelled_scope(): void
    {
        // Ref: TSD Section 2.2.1 - status = cancelled

        $reflection = new ReflectionClass(Booking::class);

        $this->assertTrue($reflection->hasMethod('scopeCancelled'));
    }

    #[Test]
    public function model_should_have_completed_scope(): void
    {
        // Ref: TSD Section 2.2.1 - status = completed

        $reflection = new ReflectionClass(Booking::class);

        $this->assertTrue($reflection->hasMethod('scopeCompleted'));
    }

    #[Test]
    public function model_should_have_for_user_scope(): void
    {
        // Ref: TSD Section 2.2.1 - 按用户筛选

        $reflection = new ReflectionClass(Booking::class);

        $this->assertTrue($reflection->hasMethod('scopeForUser'));
    }

    // =========================================================================
    // Accessor Method Tests
    // Ref: TSD Section 2.1.3, 5.2 - 计算属性
    // =========================================================================

    #[Test]
    public function model_should_have_status_label_accessor(): void
    {
        // Ref: TSD Section 2.1.3 - 状态中文标签

        $reflection = new ReflectionClass(Booking::class);

        $this->assertTrue($reflection->hasMethod('getStatusLabelAttribute'));
    }

    #[Test]
    public function model_should_have_can_cancel_accessor(): void
    {
        // Ref: TSD Section 5.2 - 可取消状态

        $reflection = new ReflectionClass(Booking::class);

        $this->assertTrue($reflection->hasMethod('getCanCancelAttribute'));
    }

    // =========================================================================
    // status_label Logic Tests (Pure Logic - No DB)
    // Ref: TSD Section 2.1.3 - 状态标签
    // =========================================================================

    #[Test]
    public function status_label_should_return_confirmed_for_confirmed_status(): void
    {
        // Ref: TSD Section 2.1.3 - 状态显示

        $booking = new Booking();
        $booking->status = 'confirmed';

        $result = $booking->getStatusLabelAttribute();

        $this->assertEquals('已确认', $result);
    }

    #[Test]
    public function status_label_should_return_cancelled_for_cancelled_status(): void
    {
        // Ref: TSD Section 2.1.4 - 取消状态

        $booking = new Booking();
        $booking->status = 'cancelled';

        $result = $booking->getStatusLabelAttribute();

        $this->assertEquals('已取消', $result);
    }

    #[Test]
    public function status_label_should_return_completed_for_completed_status(): void
    {
        // Ref: TSD Section 2.2.1 - 完成状态

        $booking = new Booking();
        $booking->status = 'completed';

        $result = $booking->getStatusLabelAttribute();

        $this->assertEquals('已完成', $result);
    }

    #[Test]
    public function status_label_should_return_unknown_for_invalid_status(): void
    {
        // Ref: TSD Section 6.3 - 异常情况处理

        $booking = new Booking();
        $booking->status = 'invalid_status';

        $result = $booking->getStatusLabelAttribute();

        $this->assertEquals('未知', $result);
    }

    #[Test]
    public function status_label_should_return_unknown_for_pending_status(): void
    {
        // Ref: TSD Section 3.1.3 - pending 不在预定义枚举中

        $booking = new Booking();
        $booking->status = 'pending';

        $result = $booking->getStatusLabelAttribute();

        $this->assertEquals('未知', $result);
    }
}
