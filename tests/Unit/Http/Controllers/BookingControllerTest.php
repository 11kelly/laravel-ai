<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\BookingController;
use App\Services\BookingService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class BookingControllerTest extends TestCase
{
    private BookingService|MockObject $bookingServiceMock;
    private BookingController $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bookingServiceMock = $this->createMock(BookingService::class);
        $this->sut = new BookingController($this->bookingServiceMock);
    }

    #[Test]
    public function map_booking_error_returns_expected_messages(): void
    {
        // Ref: TSD Section 2.3（错误码对外呈现）
        $map = new ReflectionMethod(BookingController::class, 'mapBookingError');
        $map->setAccessible(true);

        $this->assertSame('活动不存在或不可见。', $map->invoke($this->sut, 'ACTIVITY_NOT_FOUND'));
        $this->assertSame('该活动当前不可预约（未发布/已开始/已结束）。', $map->invoke($this->sut, 'ACTIVITY_NOT_BOOKABLE'));
        $this->assertSame('名额已满，请稍后再试。', $map->invoke($this->sut, 'CAPACITY_EXHAUSTED'));
        $this->assertSame('你已预约过该活动（不可重复预约）。', $map->invoke($this->sut, 'DUPLICATE_BOOKING'));
        $this->assertSame('预约记录不存在。', $map->invoke($this->sut, 'BOOKING_NOT_FOUND'));
        $this->assertSame('无权限操作该预约。', $map->invoke($this->sut, 'FORBIDDEN'));
        $this->assertSame('距离活动开始不足 1 小时或已开始，无法取消。', $map->invoke($this->sut, 'CANCEL_DEADLINE_PASSED'));
        $this->assertSame('操作失败，请稍后重试。', $map->invoke($this->sut, 'SOME_UNKNOWN_CODE'));
    }
}


