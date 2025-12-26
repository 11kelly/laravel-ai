<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Activity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Activity 模型单元测试
 *
 * 由于 Eloquent 模型依赖 Laravel 应用上下文，
 * 此测试类仅验证类结构、属性定义和方法签名。
 * 完整的 Accessor/Mutator/Scope 测试请参见 Feature Tests。
 */
#[CoversClass(Activity::class)]
class ActivityTest extends TestCase
{
    // =========================================================================
    // Model Structure Tests
    // Ref: TSD Section 3.1.2 - activities 表结构
    // =========================================================================

    #[Test]
    public function model_should_have_fillable_attributes(): void
    {
        // Ref: TSD Section 3.1.2 - 活动表字段定义

        $reflection = new ReflectionClass(Activity::class);
        $property = $reflection->getProperty('fillable');

        $activity = new Activity();
        $fillable = $activity->getFillable();

        // 验证关键字段存在
        $this->assertContains('title', $fillable);
        $this->assertContains('slug', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('content', $fillable);
        $this->assertContains('cover_image', $fillable);
        $this->assertContains('gallery', $fillable);
        $this->assertContains('start_time', $fillable);
        $this->assertContains('end_time', $fillable);
        $this->assertContains('registration_deadline', $fillable);
        $this->assertContains('location', $fillable);
        $this->assertContains('address', $fillable);
        $this->assertContains('capacity', $fillable);
        $this->assertContains('booked_count', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('is_featured', $fillable);
        $this->assertContains('created_by', $fillable);
    }

    #[Test]
    public function model_should_have_casts_defined(): void
    {
        // Ref: TSD Section 3.1.2 - 字段类型转换

        $activity = new Activity();
        $casts = $activity->getCasts();

        // 验证类型转换
        $this->assertEquals('array', $casts['gallery']);
        $this->assertEquals('datetime', $casts['start_time']);
        $this->assertEquals('datetime', $casts['end_time']);
        $this->assertEquals('datetime', $casts['registration_deadline']);
        $this->assertEquals('boolean', $casts['is_featured']);
        $this->assertEquals('integer', $casts['capacity']);
        $this->assertEquals('integer', $casts['booked_count']);
    }

    #[Test]
    public function model_should_use_soft_deletes(): void
    {
        // Ref: TSD Section 3.1.2 - deleted_at NULLABLE (软删除)

        $reflection = new ReflectionClass(Activity::class);
        $traits = $reflection->getTraitNames();

        $this->assertContains(
            'Illuminate\Database\Eloquent\SoftDeletes',
            $traits
        );
    }

    // =========================================================================
    // Relationship Method Tests
    // Ref: TSD Section 3.1.2 - 表关联关系
    // =========================================================================

    #[Test]
    public function model_should_have_creator_relationship(): void
    {
        // Ref: TSD Section 3.1.2 - created_by FK(users.id)

        $reflection = new ReflectionClass(Activity::class);

        $this->assertTrue($reflection->hasMethod('creator'));
        $method = $reflection->getMethod('creator');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function model_should_have_bookings_relationship(): void
    {
        // Ref: TSD Section 3.1.3 - 活动与预约一对多关系

        $reflection = new ReflectionClass(Activity::class);

        $this->assertTrue($reflection->hasMethod('bookings'));
        $method = $reflection->getMethod('bookings');
        $this->assertTrue($method->isPublic());
    }

    // =========================================================================
    // Scope Method Tests
    // Ref: TSD Section 2.1.1 - 活动状态筛选
    // =========================================================================

    #[Test]
    public function model_should_have_published_scope(): void
    {
        // Ref: TSD Section 2.1.1 - status = published

        $reflection = new ReflectionClass(Activity::class);

        $this->assertTrue($reflection->hasMethod('scopePublished'));
    }

    #[Test]
    public function model_should_have_upcoming_scope(): void
    {
        // Ref: TSD Section 2.1.1 - status = upcoming

        $reflection = new ReflectionClass(Activity::class);

        $this->assertTrue($reflection->hasMethod('scopeUpcoming'));
    }

    #[Test]
    public function model_should_have_ongoing_scope(): void
    {
        // Ref: TSD Section 2.1.1 - status = ongoing

        $reflection = new ReflectionClass(Activity::class);

        $this->assertTrue($reflection->hasMethod('scopeOngoing'));
    }

    #[Test]
    public function model_should_have_ended_scope(): void
    {
        // Ref: TSD Section 2.1.1 - status = ended

        $reflection = new ReflectionClass(Activity::class);

        $this->assertTrue($reflection->hasMethod('scopeEnded'));
    }

    #[Test]
    public function model_should_have_featured_scope(): void
    {
        // Ref: TSD Section 3.1.2 - is_featured 推荐活动

        $reflection = new ReflectionClass(Activity::class);

        $this->assertTrue($reflection->hasMethod('scopeFeatured'));
    }

    // =========================================================================
    // Accessor Method Tests
    // Ref: TSD Section 2.1.2 - 计算属性
    // =========================================================================

    #[Test]
    public function model_should_have_is_available_accessor(): void
    {
        // Ref: TSD Section 2.1.2 - is_available 可预约状态

        $reflection = new ReflectionClass(Activity::class);

        $this->assertTrue($reflection->hasMethod('getIsAvailableAttribute'));
    }

    #[Test]
    public function model_should_have_remaining_capacity_accessor(): void
    {
        // Ref: TSD Section 2.1.1 - capacity - booked_count

        $reflection = new ReflectionClass(Activity::class);

        $this->assertTrue($reflection->hasMethod('getRemainingCapacityAttribute'));
    }

    #[Test]
    public function model_should_have_status_label_accessor(): void
    {
        // Ref: TSD Section 2.1.1 - 状态中文标签

        $reflection = new ReflectionClass(Activity::class);

        $this->assertTrue($reflection->hasMethod('getStatusLabelAttribute'));
    }

    // =========================================================================
    // remaining_capacity Logic Tests (Pure Logic - No DB)
    // Ref: TSD Section 2.1.1 - 剩余容量计算
    // =========================================================================

    #[Test]
    public function remaining_capacity_calculation_logic(): void
    {
        // Ref: TSD Section 2.1.1 - capacity - booked_count

        $activity = new Activity();

        // 通过反射直接设置属性值
        $reflection = new ReflectionClass(Activity::class);

        // 设置 attributes 数组
        $activity->capacity = 100;
        $activity->booked_count = 30;

        // 直接调用 accessor 方法
        $result = $activity->getRemainingCapacityAttribute();

        $this->assertEquals(70, $result);
    }

    #[Test]
    public function remaining_capacity_should_not_be_negative(): void
    {
        // Ref: TSD Section 6.3 - 数据一致性保护

        $activity = new Activity();
        $activity->capacity = 100;
        $activity->booked_count = 150; // 异常情况

        $result = $activity->getRemainingCapacityAttribute();

        $this->assertEquals(0, $result);
    }

    #[Test]
    public function remaining_capacity_should_be_zero_when_full(): void
    {
        // Ref: TSD Section 5.1 - 名额已满边界

        $activity = new Activity();
        $activity->capacity = 100;
        $activity->booked_count = 100;

        $result = $activity->getRemainingCapacityAttribute();

        $this->assertEquals(0, $result);
    }

    #[Test]
    public function remaining_capacity_should_equal_capacity_when_empty(): void
    {
        // Ref: TSD Section 2.1.1 - 初始状态

        $activity = new Activity();
        $activity->capacity = 50;
        $activity->booked_count = 0;

        $result = $activity->getRemainingCapacityAttribute();

        $this->assertEquals(50, $result);
    }
}
