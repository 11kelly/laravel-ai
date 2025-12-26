<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\ActivityService;
use App\Services\BookingService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * BookingService 单元测试
 *
 * 由于 BookingService 依赖 Eloquent ORM 和数据库事务，
 * 此测试类仅验证类结构和方法签名。完整的业务流程测试请参见 Feature Tests。
 */
#[CoversClass(BookingService::class)]
class BookingServiceTest extends TestCase
{
    private BookingService $sut;

    private ActivityService|MockObject $activityServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activityServiceMock = $this->createMock(ActivityService::class);
        $this->sut = new BookingService($this->activityServiceMock);
    }

    // =========================================================================
    // Constructor & Dependency Injection Tests
    // Ref: TSD Section 2.3.2 - BookingService 依赖
    // =========================================================================

    #[Test]
    public function constructor_should_require_activity_service_dependency(): void
    {
        // Ref: TSD Section 5.1, 5.2 - 预约需要调用 ActivityService 更新 booked_count

        $reflection = new ReflectionClass(BookingService::class);
        $constructor = $reflection->getConstructor();
        $params = $constructor->getParameters();

        $this->assertCount(1, $params);
        $this->assertEquals('activityService', $params[0]->getName());
        $this->assertEquals(ActivityService::class, $params[0]->getType()->getName());
    }

    #[Test]
    public function service_should_be_instantiable_with_mock_dependency(): void
    {
        // Arrange & Act
        $service = new BookingService($this->activityServiceMock);

        // Assert
        $this->assertInstanceOf(BookingService::class, $service);
    }

    // =========================================================================
    // Method Signature Tests
    // Ref: TSD Section 2.1.3, 2.1.4, 2.2.1 - API 方法定义
    // =========================================================================

    #[Test]
    public function service_should_have_required_public_methods(): void
    {
        // Ref: TSD Section 2 - 核心接口定义

        $reflection = new ReflectionClass(BookingService::class);

        $this->assertTrue($reflection->hasMethod('getUserBookings'));
        $this->assertTrue($reflection->hasMethod('createBooking'));
        $this->assertTrue($reflection->hasMethod('cancelBooking'));
        $this->assertTrue($reflection->hasMethod('hasUserBooked'));
        $this->assertTrue($reflection->hasMethod('getUserBookingForActivity'));
    }

    #[Test]
    public function get_user_bookings_should_accept_user_status_and_per_page(): void
    {
        // Ref: TSD Section 2.2.1 - 获取用户预约列表

        $reflection = new ReflectionClass(BookingService::class);
        $method = $reflection->getMethod('getUserBookings');
        $params = $method->getParameters();

        $this->assertEquals('user', $params[0]->getName());
        $this->assertEquals('status', $params[1]->getName());
        $this->assertTrue($params[1]->allowsNull());
        $this->assertEquals('perPage', $params[2]->getName());
        $this->assertEquals(10, $params[2]->getDefaultValue()); // 默认 10 条/页
    }

    #[Test]
    public function create_booking_should_accept_required_parameters(): void
    {
        // Ref: TSD Section 2.1.3 - 预约活动接口参数

        $reflection = new ReflectionClass(BookingService::class);
        $method = $reflection->getMethod('createBooking');
        $params = $method->getParameters();

        $this->assertEquals('user', $params[0]->getName());
        $this->assertEquals('activity', $params[1]->getName());
        $this->assertEquals('participants', $params[2]->getName());
        $this->assertEquals(1, $params[2]->getDefaultValue()); // 默认 1 人
        $this->assertEquals('remarks', $params[3]->getName());
        $this->assertTrue($params[3]->allowsNull());
    }

    #[Test]
    public function cancel_booking_should_accept_booking_and_reason(): void
    {
        // Ref: TSD Section 2.1.4 - 取消预约接口

        $reflection = new ReflectionClass(BookingService::class);
        $method = $reflection->getMethod('cancelBooking');
        $params = $method->getParameters();

        $this->assertEquals('booking', $params[0]->getName());
        $this->assertEquals('reason', $params[1]->getName());
        $this->assertTrue($params[1]->allowsNull());
    }

    #[Test]
    public function has_user_booked_should_accept_user_and_activity(): void
    {
        // Ref: TSD Section 5.1 - 用户已预约过检查

        $reflection = new ReflectionClass(BookingService::class);
        $method = $reflection->getMethod('hasUserBooked');
        $params = $method->getParameters();

        $this->assertEquals('user', $params[0]->getName());
        $this->assertEquals('activity', $params[1]->getName());
    }

    #[Test]
    public function get_user_booking_for_activity_should_accept_user_and_activity(): void
    {
        // Ref: TSD Section 5.1 - 获取用户对特定活动的预约

        $reflection = new ReflectionClass(BookingService::class);
        $method = $reflection->getMethod('getUserBookingForActivity');
        $params = $method->getParameters();

        $this->assertEquals('user', $params[0]->getName());
        $this->assertEquals('activity', $params[1]->getName());
    }

    #[Test]
    public function create_booking_should_return_array(): void
    {
        // Ref: TSD Section 2.1.3 - 返回结构包含 success, message, booking

        $reflection = new ReflectionClass(BookingService::class);
        $method = $reflection->getMethod('createBooking');
        $returnType = $method->getReturnType();

        $this->assertEquals('array', $returnType->getName());
    }

    #[Test]
    public function cancel_booking_should_return_array(): void
    {
        // Ref: TSD Section 2.1.4 - 返回结构包含 success, message

        $reflection = new ReflectionClass(BookingService::class);
        $method = $reflection->getMethod('cancelBooking');
        $returnType = $method->getReturnType();

        $this->assertEquals('array', $returnType->getName());
    }

    #[Test]
    public function has_user_booked_should_return_boolean(): void
    {
        // Ref: TSD Section 6.2 - 重复预约检查

        $reflection = new ReflectionClass(BookingService::class);
        $method = $reflection->getMethod('hasUserBooked');
        $returnType = $method->getReturnType();

        $this->assertEquals('bool', $returnType->getName());
    }

    // =========================================================================
    // Protected Method Tests (via Reflection)
    // Ref: TSD Section 5.1 - 预约验证逻辑
    // =========================================================================

    #[Test]
    public function validate_booking_protected_method_should_exist(): void
    {
        // Ref: TSD Section 5.1 - 检查活动状态

        $reflection = new ReflectionClass(BookingService::class);

        $this->assertTrue($reflection->hasMethod('validateBooking'));
        $method = $reflection->getMethod('validateBooking');
        $this->assertTrue($method->isProtected());
    }

    #[Test]
    public function validate_booking_should_accept_activity_and_participants(): void
    {
        // Ref: TSD Section 5.1 - 验证名额、状态、截止时间

        $reflection = new ReflectionClass(BookingService::class);
        $method = $reflection->getMethod('validateBooking');
        $params = $method->getParameters();

        $this->assertEquals('activity', $params[0]->getName());
        $this->assertEquals('participants', $params[1]->getName());
    }
}
