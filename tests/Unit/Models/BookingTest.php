<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class BookingTest extends TestCase
{
    private Builder|MockObject $builderMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builderMock = $this->createMock(Builder::class);
    }

    #[Test]
    public function scope_active_applies_status_active_constraint(): void
    {
        // Ref: TSD Section 3.4（bookings.status: active/cancelled）
        $this->builderMock
            ->expects($this->once())
            ->method('where')
            ->with('status', '=', 'active')
            ->willReturnSelf();

        $booking = new Booking();
        // 避免在无数据库连接的纯单测环境下触发 Model::connection()
        $booking->setDateFormat('Y-m-d H:i:s');
        $booking->scopeActive($this->builderMock);
    }
}


