<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Activity 模型功能测试
 *
 * 测试 Accessor、Scope 等需要 Laravel 应用上下文的功能
 */
#[CoversClass(Activity::class)]
class ActivityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    // =========================================================================
    // is_available Accessor Tests
    // Ref: TSD Section 2.1.2 - is_available 字段定义
    // =========================================================================

    #[Test]
    public function is_available_should_return_true_when_all_conditions_met(): void
    {
        // Ref: TSD Section 2.1.2 - 活动可预约条件

        $activity = Activity::factory()->create([
            'status' => 'published',
            'capacity' => 100,
            'booked_count' => 50,
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'registration_deadline' => now()->addDays(5),
            'created_by' => $this->admin->id,
        ]);

        $this->assertTrue($activity->is_available);
    }

    #[Test]
    public function is_available_should_return_false_when_draft(): void
    {
        // Ref: TSD Section 5.1 - 活动状态检查

        $activity = Activity::factory()->create([
            'status' => 'draft',
            'capacity' => 100,
            'booked_count' => 0,
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'created_by' => $this->admin->id,
        ]);

        $this->assertFalse($activity->is_available);
    }

    #[Test]
    public function is_available_should_return_false_when_full(): void
    {
        // Ref: TSD Section 5.1 - 名额已满

        $activity = Activity::factory()->create([
            'status' => 'published',
            'capacity' => 100,
            'booked_count' => 100,
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'created_by' => $this->admin->id,
        ]);

        $this->assertFalse($activity->is_available);
    }

    #[Test]
    public function is_available_should_return_false_when_deadline_passed(): void
    {
        // Ref: TSD Section 5.1 - 已过报名截止时间

        $activity = Activity::factory()->create([
            'status' => 'published',
            'capacity' => 100,
            'booked_count' => 50,
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'registration_deadline' => now()->subHours(1),
            'created_by' => $this->admin->id,
        ]);

        $this->assertFalse($activity->is_available);
    }

    #[Test]
    public function is_available_should_return_false_when_started(): void
    {
        // Ref: TSD Section 5.1 - 活动已开始

        $activity = Activity::factory()->create([
            'status' => 'published',
            'capacity' => 100,
            'booked_count' => 50,
            'start_time' => now()->subHours(1),
            'end_time' => now()->addHours(2),
            'created_by' => $this->admin->id,
        ]);

        $this->assertFalse($activity->is_available);
    }

    #[Test]
    public function is_available_should_return_true_without_deadline(): void
    {
        // Ref: TSD Section 3.1.2 - registration_deadline NULLABLE

        $activity = Activity::factory()->create([
            'status' => 'published',
            'capacity' => 100,
            'booked_count' => 50,
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'registration_deadline' => null,
            'created_by' => $this->admin->id,
        ]);

        $this->assertTrue($activity->is_available);
    }

    // =========================================================================
    // status_label Accessor Tests
    // Ref: TSD Section 2.1.1 - 状态标签
    // =========================================================================

    #[Test]
    public function status_label_should_return_ended(): void
    {
        // Ref: TSD Section 2.1.1 - 已结束

        $activity = Activity::factory()->create([
            'start_time' => now()->subDays(2),
            'end_time' => now()->subDays(1),
            'capacity' => 100,
            'booked_count' => 50,
            'created_by' => $this->admin->id,
        ]);

        $this->assertEquals('已结束', $activity->status_label);
    }

    #[Test]
    public function status_label_should_return_ongoing(): void
    {
        // Ref: TSD Section 2.1.1 - 进行中

        $activity = Activity::factory()->create([
            'start_time' => now()->subHours(1),
            'end_time' => now()->addHours(2),
            'capacity' => 100,
            'booked_count' => 50,
            'created_by' => $this->admin->id,
        ]);

        $this->assertEquals('进行中', $activity->status_label);
    }

    #[Test]
    public function status_label_should_return_full(): void
    {
        // Ref: TSD Section 5.1 - 名额已满

        $activity = Activity::factory()->create([
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'capacity' => 100,
            'booked_count' => 100,
            'created_by' => $this->admin->id,
        ]);

        $this->assertEquals('已满', $activity->status_label);
    }

    #[Test]
    public function status_label_should_return_registration_closed(): void
    {
        // Ref: TSD Section 5.1 - 报名截止

        $activity = Activity::factory()->create([
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'capacity' => 100,
            'booked_count' => 50,
            'registration_deadline' => now()->subHours(1),
            'created_by' => $this->admin->id,
        ]);

        $this->assertEquals('报名截止', $activity->status_label);
    }

    #[Test]
    public function status_label_should_return_available(): void
    {
        // Ref: TSD Section 2.1.2 - 可预约状态

        $activity = Activity::factory()->create([
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'capacity' => 100,
            'booked_count' => 50,
            'registration_deadline' => now()->addDays(5),
            'created_by' => $this->admin->id,
        ]);

        $this->assertEquals('可预约', $activity->status_label);
    }

    // =========================================================================
    // Scope Tests
    // Ref: TSD Section 2.1.1 - 活动筛选
    // =========================================================================

    #[Test]
    public function scope_published_should_filter_by_status(): void
    {
        // Ref: TSD Section 2.1.1 - status = published

        Activity::factory()->create(['status' => 'published', 'created_by' => $this->admin->id]);
        Activity::factory()->create(['status' => 'draft', 'created_by' => $this->admin->id]);
        Activity::factory()->create(['status' => 'cancelled', 'created_by' => $this->admin->id]);

        $this->assertCount(1, Activity::published()->get());
    }

    #[Test]
    public function scope_upcoming_should_filter_by_start_time(): void
    {
        // Ref: TSD Section 2.1.1 - status = upcoming

        Activity::factory()->create([
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'created_by' => $this->admin->id,
        ]);
        Activity::factory()->create([
            'start_time' => now()->subDays(1),
            'end_time' => now()->addDays(1),
            'created_by' => $this->admin->id,
        ]);

        $this->assertCount(1, Activity::upcoming()->get());
    }

    #[Test]
    public function scope_ongoing_should_filter_current_activities(): void
    {
        // Ref: TSD Section 2.1.1 - status = ongoing

        Activity::factory()->create([
            'start_time' => now()->subHours(1),
            'end_time' => now()->addHours(2),
            'created_by' => $this->admin->id,
        ]);
        Activity::factory()->create([
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'created_by' => $this->admin->id,
        ]);

        $this->assertCount(1, Activity::ongoing()->get());
    }

    #[Test]
    public function scope_ended_should_filter_past_activities(): void
    {
        // Ref: TSD Section 2.1.1 - status = ended

        Activity::factory()->create([
            'start_time' => now()->subDays(2),
            'end_time' => now()->subDays(1),
            'created_by' => $this->admin->id,
        ]);
        Activity::factory()->create([
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'created_by' => $this->admin->id,
        ]);

        $this->assertCount(1, Activity::ended()->get());
    }

    #[Test]
    public function scope_featured_should_filter_by_is_featured(): void
    {
        // Ref: TSD Section 3.1.2 - is_featured 推荐活动

        Activity::factory()->create(['is_featured' => true, 'created_by' => $this->admin->id]);
        Activity::factory()->create(['is_featured' => false, 'created_by' => $this->admin->id]);

        $this->assertCount(1, Activity::featured()->get());
    }

    // =========================================================================
    // Relationship Tests
    // Ref: TSD Section 3.1.2 - 表关联关系
    // =========================================================================

    #[Test]
    public function creator_relationship_should_return_user(): void
    {
        // Ref: TSD Section 3.1.2 - created_by FK(users.id)

        $activity = Activity::factory()->create([
            'created_by' => $this->admin->id,
        ]);

        $this->assertInstanceOf(User::class, $activity->creator);
        $this->assertEquals($this->admin->id, $activity->creator->id);
    }

    // =========================================================================
    // Auto Slug Generation Tests
    // Ref: TSD Section 3.1.2 - slug 自动生成
    // =========================================================================

    #[Test]
    public function slug_should_be_auto_generated_if_empty(): void
    {
        // Ref: TSD Section 3.1.2 - UNIQUE(slug)

        $activity = Activity::create([
            'title' => '测试活动标题',
            'capacity' => 100,
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'location' => '测试地点',
            'created_by' => $this->admin->id,
        ]);

        $this->assertNotNull($activity->slug);
        $this->assertNotEmpty($activity->slug);
    }
}

