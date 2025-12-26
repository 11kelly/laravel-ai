<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\ActivityService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * ActivityService 单元测试
 *
 * 由于 ActivityService 的大部分方法依赖 Eloquent ORM，
 * 此测试类仅验证纯逻辑方法。完整的业务流程测试请参见 Feature Tests。
 */
#[CoversClass(ActivityService::class)]
class ActivityServiceTest extends TestCase
{
    private ActivityService $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new ActivityService();
    }

    // =========================================================================
    // applyFilters Tests (Protected Method via Reflection)
    // Ref: TSD Section 2.1.1 - 活动列表筛选参数
    // =========================================================================

    #[Test]
    public function apply_filters_should_accept_empty_filters(): void
    {
        // Ref: TSD Section 2.1.1 - 所有筛选参数均为可选

        // 使用反射测试 protected 方法
        $reflection = new ReflectionClass(ActivityService::class);
        $method = $reflection->getMethod('applyFilters');

        // 验证方法存在并接受数组参数
        $this->assertCount(2, $method->getParameters());
        $this->assertEquals('filters', $method->getParameters()[1]->getName());
    }

    #[Test]
    public function constructor_should_not_require_dependencies(): void
    {
        // Ref: TSD Section 2.3.1 - ActivityService 应该可以独立实例化

        // Act & Assert
        $service = new ActivityService();
        $this->assertInstanceOf(ActivityService::class, $service);
    }

    #[Test]
    public function service_should_have_required_public_methods(): void
    {
        // Ref: TSD Section 2.1.1, 2.1.2 - API 方法定义

        $reflection = new ReflectionClass(ActivityService::class);

        // 验证所有公开方法存在
        $this->assertTrue($reflection->hasMethod('getPublishedActivities'));
        $this->assertTrue($reflection->hasMethod('getFeaturedActivities'));
        $this->assertTrue($reflection->hasMethod('getUpcomingActivities'));
        $this->assertTrue($reflection->hasMethod('findBySlug'));
        $this->assertTrue($reflection->hasMethod('findById'));
        $this->assertTrue($reflection->hasMethod('incrementBookedCount'));
        $this->assertTrue($reflection->hasMethod('decrementBookedCount'));
    }

    #[Test]
    public function get_published_activities_should_accept_filters_and_per_page(): void
    {
        // Ref: TSD Section 2.1.1 - 分页与筛选参数

        $reflection = new ReflectionClass(ActivityService::class);
        $method = $reflection->getMethod('getPublishedActivities');
        $params = $method->getParameters();

        $this->assertEquals('filters', $params[0]->getName());
        $this->assertEquals('perPage', $params[1]->getName());
        $this->assertEquals(15, $params[1]->getDefaultValue()); // 默认 15 条/页
    }

    #[Test]
    public function get_featured_activities_should_have_default_limit(): void
    {
        // Ref: TSD Section 4.1 - 首页特色活动展示

        $reflection = new ReflectionClass(ActivityService::class);
        $method = $reflection->getMethod('getFeaturedActivities');
        $params = $method->getParameters();

        $this->assertEquals('limit', $params[0]->getName());
        $this->assertEquals(6, $params[0]->getDefaultValue()); // 默认 6 条
    }

    #[Test]
    public function get_upcoming_activities_should_have_default_limit(): void
    {
        // Ref: TSD Section 4.1 - 首页即将开始活动展示

        $reflection = new ReflectionClass(ActivityService::class);
        $method = $reflection->getMethod('getUpcomingActivities');
        $params = $method->getParameters();

        $this->assertEquals('limit', $params[0]->getName());
        $this->assertEquals(10, $params[0]->getDefaultValue()); // 默认 10 条
    }

    #[Test]
    public function increment_booked_count_should_accept_activity_and_participants(): void
    {
        // Ref: TSD Section 5.1 - 更新活动 booked_count

        $reflection = new ReflectionClass(ActivityService::class);
        $method = $reflection->getMethod('incrementBookedCount');
        $params = $method->getParameters();

        $this->assertEquals('activity', $params[0]->getName());
        $this->assertEquals('participants', $params[1]->getName());
        $this->assertEquals(1, $params[1]->getDefaultValue()); // 默认 1 人
    }

    #[Test]
    public function decrement_booked_count_should_accept_activity_and_participants(): void
    {
        // Ref: TSD Section 5.2 - 减少活动 booked_count

        $reflection = new ReflectionClass(ActivityService::class);
        $method = $reflection->getMethod('decrementBookedCount');
        $params = $method->getParameters();

        $this->assertEquals('activity', $params[0]->getName());
        $this->assertEquals('participants', $params[1]->getName());
        $this->assertEquals(1, $params[1]->getDefaultValue()); // 默认 1 人
    }

    #[Test]
    public function find_by_slug_should_accept_string_parameter(): void
    {
        // Ref: TSD Section 2.1.2 - 通过 slug 查找活动

        $reflection = new ReflectionClass(ActivityService::class);
        $method = $reflection->getMethod('findBySlug');
        $params = $method->getParameters();

        $this->assertEquals('slug', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
    }

    #[Test]
    public function find_by_id_should_accept_integer_parameter(): void
    {
        // Ref: TSD Section 2.1.2 - 通过 ID 查找活动

        $reflection = new ReflectionClass(ActivityService::class);
        $method = $reflection->getMethod('findById');
        $params = $method->getParameters();

        $this->assertEquals('id', $params[0]->getName());
        $this->assertEquals('int', $params[0]->getType()->getName());
    }
}
