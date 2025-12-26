<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\Activity;
use App\Models\User;
use App\Services\ActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * ActivityService 功能测试
 *
 * 测试与数据库交互的业务逻辑
 */
#[CoversClass(ActivityService::class)]
class ActivityServiceTest extends TestCase
{
    use RefreshDatabase;

    private ActivityService $service;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ActivityService();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    // =========================================================================
    // getPublishedActivities Tests
    // Ref: TSD Section 2.1.1 - 活动列表接口
    // =========================================================================

    #[Test]
    public function get_published_activities_should_return_only_published(): void
    {
        // Ref: TSD Section 2.1.1 - 只返回已发布活动

        // Arrange
        Activity::factory()->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        Activity::factory()->create([
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);
        Activity::factory()->create([
            'status' => 'cancelled',
            'created_by' => $this->admin->id,
        ]);

        // Act
        $result = $this->service->getPublishedActivities();

        // Assert
        $this->assertCount(1, $result);
    }

    #[Test]
    public function get_published_activities_should_support_pagination(): void
    {
        // Ref: TSD Section 2.1.1 - 分页支持

        // Arrange
        Activity::factory()->count(20)->create([
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);

        // Act
        $result = $this->service->getPublishedActivities([], 5);

        // Assert
        $this->assertEquals(5, $result->perPage());
        $this->assertEquals(20, $result->total());
        $this->assertEquals(4, $result->lastPage());
    }

    #[Test]
    public function get_published_activities_should_filter_by_search(): void
    {
        // Ref: TSD Section 2.1.1 - search 参数

        // Arrange
        Activity::factory()->create([
            'title' => '瑜伽课程',
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
        Activity::factory()->create([
            'title' => '编程工作坊',
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);

        // Act
        $result = $this->service->getPublishedActivities(['search' => '瑜伽']);

        // Assert
        $this->assertCount(1, $result);
        $this->assertEquals('瑜伽课程', $result->first()->title);
    }

    #[Test]
    public function get_published_activities_should_filter_by_status_upcoming(): void
    {
        // Ref: TSD Section 2.1.1 - status = upcoming

        // Arrange
        Activity::factory()->create([
            'status' => 'published',
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'created_by' => $this->admin->id,
        ]);
        Activity::factory()->create([
            'status' => 'published',
            'start_time' => now()->subDays(1),
            'end_time' => now()->addDays(1),
            'created_by' => $this->admin->id,
        ]);

        // Act
        $result = $this->service->getPublishedActivities(['status' => 'upcoming']);

        // Assert
        $this->assertCount(1, $result);
    }

    // =========================================================================
    // getFeaturedActivities Tests
    // Ref: TSD Section 4.1 - 首页特色活动
    // =========================================================================

    #[Test]
    public function get_featured_activities_should_return_only_featured(): void
    {
        // Ref: TSD Section 4.1 - 特色活动展示

        // Arrange
        Activity::factory()->create([
            'status' => 'published',
            'is_featured' => true,
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'created_by' => $this->admin->id,
        ]);
        Activity::factory()->create([
            'status' => 'published',
            'is_featured' => false,
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'created_by' => $this->admin->id,
        ]);

        // Act
        $result = $this->service->getFeaturedActivities();

        // Assert
        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->is_featured);
    }

    #[Test]
    public function get_featured_activities_should_respect_limit(): void
    {
        // Ref: TSD Section 4.1 - 限制数量

        // Arrange
        Activity::factory()->count(10)->create([
            'status' => 'published',
            'is_featured' => true,
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(8),
            'created_by' => $this->admin->id,
        ]);

        // Act
        $result = $this->service->getFeaturedActivities(3);

        // Assert
        $this->assertCount(3, $result);
    }

    // =========================================================================
    // incrementBookedCount / decrementBookedCount Tests
    // Ref: TSD Section 5.1, 5.2 - 预约人数更新
    // =========================================================================

    #[Test]
    public function increment_booked_count_should_increase_count(): void
    {
        // Ref: TSD Section 5.1 - 更新活动 booked_count

        // Arrange
        $activity = Activity::factory()->create([
            'booked_count' => 10,
            'capacity' => 100,
            'created_by' => $this->admin->id,
        ]);

        // Act
        $result = $this->service->incrementBookedCount($activity, 3);
        $activity->refresh();

        // Assert
        $this->assertTrue($result);
        $this->assertEquals(13, $activity->booked_count);
    }

    #[Test]
    public function decrement_booked_count_should_decrease_count(): void
    {
        // Ref: TSD Section 5.2 - 减少活动 booked_count

        // Arrange
        $activity = Activity::factory()->create([
            'booked_count' => 10,
            'capacity' => 100,
            'created_by' => $this->admin->id,
        ]);

        // Act
        $result = $this->service->decrementBookedCount($activity, 3);
        $activity->refresh();

        // Assert
        $this->assertTrue($result);
        $this->assertEquals(7, $activity->booked_count);
    }

    #[Test]
    public function decrement_booked_count_should_fail_when_insufficient(): void
    {
        // Ref: TSD Section 6.3 - 数据一致性保护

        // Arrange
        $activity = Activity::factory()->create([
            'booked_count' => 2,
            'capacity' => 100,
            'created_by' => $this->admin->id,
        ]);

        // Act
        $result = $this->service->decrementBookedCount($activity, 5);
        $activity->refresh();

        // Assert
        $this->assertFalse($result);
        $this->assertEquals(2, $activity->booked_count); // 保持不变
    }

    // =========================================================================
    // findBySlug / findById Tests
    // Ref: TSD Section 2.1.2 - 活动详情
    // =========================================================================

    #[Test]
    public function find_by_slug_should_return_published_activity(): void
    {
        // Ref: TSD Section 2.1.2 - 通过 slug 查找

        // Arrange
        $activity = Activity::factory()->create([
            'slug' => 'test-activity-123',
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);

        // Act
        $result = $this->service->findBySlug('test-activity-123');

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($activity->id, $result->id);
    }

    #[Test]
    public function find_by_slug_should_return_null_for_draft(): void
    {
        // Ref: TSD Section 2.1.2 - 非发布状态不可见

        // Arrange
        Activity::factory()->create([
            'slug' => 'draft-activity',
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);

        // Act
        $result = $this->service->findBySlug('draft-activity');

        // Assert
        $this->assertNull($result);
    }

    #[Test]
    public function find_by_id_should_return_activity_regardless_of_status(): void
    {
        // Ref: TSD Section 2.3.1 - 后台可查看所有状态

        // Arrange
        $activity = Activity::factory()->create([
            'status' => 'draft',
            'created_by' => $this->admin->id,
        ]);

        // Act
        $result = $this->service->findById($activity->id);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($activity->id, $result->id);
    }
}

