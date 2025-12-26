<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ActivityTest extends TestCase
{
    private Builder|MockObject $builderMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builderMock = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['where'])
            ->addMethods(['whereNotNull'])
            ->getMock();
    }

    #[Test]
    public function is_publicly_visible_returns_false_when_not_published(): void
    {
        // Ref: TSD Section 10.2 / 2.0 当前实现对照
        $activity = $this->makeActivity([
            'status' => 'draft',
            'published_at' => Carbon::parse('2026-01-01 10:00:00'),
        ]);

        $this->assertFalse($activity->isPubliclyVisible(Carbon::parse('2026-01-01 10:00:00')));
    }

    #[Test]
    public function is_publicly_visible_returns_false_when_published_at_missing(): void
    {
        // Ref: TSD Section 2.2 / 10.2
        $activity = $this->makeActivity([
            'status' => 'published',
            'published_at' => null,
        ]);

        $this->assertFalse($activity->isPubliclyVisible(Carbon::parse('2026-01-01 10:00:00')));
    }

    #[Test]
    public function is_publicly_visible_returns_false_when_now_before_published_at(): void
    {
        // Ref: TSD Section 10.2（现状：now < published_at => 前台不可见）
        $activity = $this->makeActivity([
            'status' => 'published',
            'published_at' => Carbon::parse('2026-01-02 10:00:00'),
        ]);

        $this->assertFalse($activity->isPubliclyVisible(Carbon::parse('2026-01-02 09:59:59')));
    }

    #[Test]
    public function is_publicly_visible_returns_true_when_published_and_published_at_reached(): void
    {
        // Ref: TSD Section 2.2 / 3.2 / 10.2
        $activity = $this->makeActivity([
            'status' => 'published',
            'published_at' => Carbon::parse('2026-01-02 10:00:00'),
        ]);

        $this->assertTrue($activity->isPubliclyVisible(Carbon::parse('2026-01-02 10:00:00')));
    }

    #[Test]
    public function remaining_capacity_is_capacity_minus_booked_count_but_not_below_zero(): void
    {
        // Ref: TSD Section 3.2（booked_count/capacity）
        $activity = $this->makeActivity([
            'capacity' => 10,
            'booked_count' => 3,
        ]);
        $this->assertSame(7, $activity->remainingCapacity());

        $activity = $this->makeActivity([
            'capacity' => 10,
            'booked_count' => 999,
        ]);
        $this->assertSame(0, $activity->remainingCapacity());
    }

    #[Test]
    public function is_bookable_returns_false_when_not_publicly_visible(): void
    {
        // Ref: TSD Section 2.3（可预约门槛）
        $activity = $this->makeActivity([
            'status' => 'draft',
            'published_at' => Carbon::parse('2026-01-01 00:00:00'),
            'starts_at' => Carbon::parse('2026-01-10 00:00:00'),
            'capacity' => 10,
            'booked_count' => 0,
        ]);

        $this->assertFalse($activity->isBookable(Carbon::parse('2026-01-02 00:00:00')));
    }

    #[Test]
    public function is_bookable_returns_false_when_missing_starts_at(): void
    {
        // Ref: TSD Section 2.3（now < starts_at 才可预约）
        $activity = $this->makeActivity([
            'status' => 'published',
            'published_at' => Carbon::parse('2026-01-01 00:00:00'),
            'starts_at' => null,
            'capacity' => 10,
            'booked_count' => 0,
        ]);

        $this->assertFalse($activity->isBookable(Carbon::parse('2026-01-02 00:00:00')));
    }

    #[Test]
    public function is_bookable_returns_false_when_now_is_on_or_after_starts_at(): void
    {
        // Ref: TSD Section 2.3（now >= starts_at 禁止预约）
        $activity = $this->makeActivity([
            'status' => 'published',
            'published_at' => Carbon::parse('2026-01-01 00:00:00'),
            'starts_at' => Carbon::parse('2026-01-10 00:00:00'),
            'capacity' => 10,
            'booked_count' => 0,
        ]);

        $this->assertFalse($activity->isBookable(Carbon::parse('2026-01-10 00:00:00')));
    }

    #[Test]
    public function is_bookable_returns_false_when_capacity_exhausted(): void
    {
        // Ref: TSD Section 2.3（CAPACITY_EXHAUSTED）
        $activity = $this->makeActivity([
            'status' => 'published',
            'published_at' => Carbon::parse('2026-01-01 00:00:00'),
            'starts_at' => Carbon::parse('2026-01-10 00:00:00'),
            'capacity' => 10,
            'booked_count' => 10,
        ]);

        $this->assertFalse($activity->isBookable(Carbon::parse('2026-01-02 00:00:00')));
    }

    #[Test]
    public function is_bookable_returns_true_when_all_conditions_met(): void
    {
        // Ref: TSD Section 2.3 / 10.2（published & published_at<=now & now<starts_at & remaining>0）
        $activity = $this->makeActivity([
            'status' => 'published',
            'published_at' => Carbon::parse('2026-01-01 00:00:00'),
            'starts_at' => Carbon::parse('2026-01-10 00:00:00'),
            'capacity' => 10,
            'booked_count' => 9,
        ]);

        $this->assertTrue($activity->isBookable(Carbon::parse('2026-01-02 00:00:00')));
    }

    #[Test]
    public function booking_disabled_reason_returns_expected_messages_in_current_implementation(): void
    {
        // Ref: TSD Section 2.3（现状：按钮禁用原因）
        $now = Carbon::parse('2026-01-02 00:00:00');

        $activity = $this->makeActivity([
            'status' => 'draft',
        ]);
        $this->assertSame('未发布', $activity->bookingDisabledReason($now));

        $activity = $this->makeActivity([
            'status' => 'published',
            'published_at' => Carbon::parse('2026-01-03 00:00:00'),
        ]);
        $this->assertSame('未到发布时间', $activity->bookingDisabledReason($now));

        $activity = $this->makeActivity([
            'status' => 'published',
            'published_at' => Carbon::parse('2026-01-01 00:00:00'),
            'starts_at' => Carbon::parse('2026-01-02 00:00:00'),
        ]);
        $this->assertSame('活动已开始', $activity->bookingDisabledReason($now));

        $activity = $this->makeActivity([
            'status' => 'published',
            'published_at' => Carbon::parse('2026-01-01 00:00:00'),
            'starts_at' => Carbon::parse('2026-01-10 00:00:00'),
            'capacity' => 1,
            'booked_count' => 1,
        ]);
        $this->assertSame('名额已满', $activity->bookingDisabledReason($now));

        $activity = $this->makeActivity([
            'status' => 'published',
            'published_at' => Carbon::parse('2026-01-01 00:00:00'),
            'starts_at' => Carbon::parse('2026-01-10 00:00:00'),
            'capacity' => 2,
            'booked_count' => 1,
        ]);
        $this->assertNull($activity->bookingDisabledReason($now));
    }

    #[Test]
    public function scope_publicly_visible_applies_published_and_published_at_lte_now_constraints(): void
    {
        // Ref: TSD Section 2.2（现状：仅对外可见 published 且 published_at<=now）
        $now = Carbon::parse('2026-01-02 12:00:00');

        $whereCalls = [];
        $this->builderMock
            ->expects($this->exactly(2))
            ->method('where')
            ->willReturnCallback(function (...$args) use (&$whereCalls) {
                $whereCalls[] = $args;
                return $this->builderMock;
            });

        $this->builderMock
            ->expects($this->once())
            ->method('whereNotNull')
            ->with('published_at')
            ->willReturnSelf();

        $activity = $this->makeActivity([]);
        $activity->scopePubliclyVisible($this->builderMock, $now);

        $this->assertSame(
            [
                ['status', '=', 'published', 'and'],
                ['published_at', '<=', $now, 'and'],
            ],
            $whereCalls,
        );
    }

    #[Test]
    public function scope_published_applies_status_published_constraint(): void
    {
        // Ref: TSD Section 3.2（activities.status）
        $this->builderMock
            ->expects($this->once())
            ->method('where')
            ->with('status', '=', 'published')
            ->willReturnSelf();

        $activity = $this->makeActivity([]);
        $activity->scopePublished($this->builderMock);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function makeActivity(array $overrides): Activity
    {
        $defaults = [
            'status' => 'draft',
            'published_at' => null,
            'starts_at' => null,
            'capacity' => 1,
            'booked_count' => 0,
        ];

        $data = array_merge($defaults, $overrides);

        $activity = new Activity();
        // 避免在无数据库连接的纯单测环境下触发 Model::connection()
        $activity->setDateFormat('Y-m-d H:i:s');
        foreach ($data as $k => $v) {
            $activity->{$k} = $v;
        }

        return $activity;
    }
}


