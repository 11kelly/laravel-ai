<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Activity;
use App\Services\ActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityServiceTest extends TestCase
{
    use RefreshDatabase;

    private ActivityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ActivityService();
    }

    /**
     * Test getPaginatedActivities returns only published activities by default.
     */
    public function test_get_paginated_activities_returns_only_published_by_default(): void
    {
        Activity::factory()->count(3)->create(['status' => 'draft']);
        Activity::factory()->count(2)->published()->create();

        $result = $this->service->getPaginatedActivities([], onlyPublished: true);

        $this->assertCount(2, $result->items());
        $this->assertTrue($result->every(fn ($activity) => $activity->status === 'published'));
    }

    /**
     * Test getPaginatedActivities filters by status.
     */
    public function test_get_paginated_activities_filters_by_status(): void
    {
        Activity::factory()->count(2)->create(['status' => 'draft']);
        Activity::factory()->count(3)->published()->create();

        $result = $this->service->getPaginatedActivities(['status' => 'draft'], onlyPublished: false);

        $this->assertCount(2, $result->items());
        $this->assertTrue($result->every(fn ($activity) => $activity->status === 'draft'));
    }

    /**
     * Test getPaginatedActivities filters by temporal status.
     */
    public function test_get_paginated_activities_filters_by_temporal_status(): void
    {
        Activity::factory()->count(2)->published()->upcoming()->create();
        Activity::factory()->count(1)->published()->ongoing()->create();
        Activity::factory()->count(1)->published()->ended()->create();

        $result = $this->service->getPaginatedActivities(['temporal_status' => 'upcoming'], onlyPublished: true);

        $this->assertCount(2, $result->items());
    }

    /**
     * Test getPaginatedActivities filters by date range.
     */
    public function test_get_paginated_activities_filters_by_date_range(): void
    {
        $dateFrom = now()->addDays(5);
        $dateTo = now()->addDays(15);

        // Activity outside range (before date_from)
        Activity::factory()->published()->create([
            'start_time' => now()->addDays(3),
            'end_time' => now()->addDays(4),
        ]);
        // Activity within range
        Activity::factory()->published()->create([
            'start_time' => now()->addDays(10),
            'end_time' => now()->addDays(12),
        ]);
        // Activity outside range (after date_to)
        Activity::factory()->published()->create([
            'start_time' => now()->addDays(20),
            'end_time' => now()->addDays(22),
        ]);

        $result = $this->service->getPaginatedActivities([
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
        ], onlyPublished: true);

        $this->assertCount(1, $result->items());
    }

    /**
     * Test getPaginatedActivities respects per_page parameter.
     */
    public function test_get_paginated_activities_respects_per_page_parameter(): void
    {
        Activity::factory()->count(25)->published()->create();

        $result = $this->service->getPaginatedActivities(['per_page' => 10], onlyPublished: true);

        $this->assertEquals(10, $result->perPage());
        $this->assertCount(10, $result->items());
    }

    /**
     * Test getPaginatedActivities limits per_page to 50.
     */
    public function test_get_paginated_activities_limits_per_page_to_50(): void
    {
        Activity::factory()->count(60)->published()->create();

        $result = $this->service->getPaginatedActivities(['per_page' => 100], onlyPublished: true);

        $this->assertEquals(50, $result->perPage());
    }

    /**
     * Test getActivityById returns activity when exists.
     */
    public function test_get_activity_by_id_returns_activity_when_exists(): void
    {
        $activity = Activity::factory()->published()->create();

        $result = $this->service->getActivityById($activity->id, onlyPublished: true);

        $this->assertNotNull($result);
        $this->assertEquals($activity->id, $result->id);
    }

    /**
     * Test getActivityById returns null for non-published activity when onlyPublished is true.
     */
    public function test_get_activity_by_id_returns_null_for_non_published_when_only_published_is_true(): void
    {
        $activity = Activity::factory()->create(['status' => 'draft']);

        $result = $this->service->getActivityById($activity->id, onlyPublished: true);

        $this->assertNull($result);
    }

    /**
     * Test getActivityById returns activity when onlyPublished is false.
     */
    public function test_get_activity_by_id_returns_activity_when_only_published_is_false(): void
    {
        $activity = Activity::factory()->create(['status' => 'draft']);

        $result = $this->service->getActivityById($activity->id, onlyPublished: false);

        $this->assertNotNull($result);
        $this->assertEquals($activity->id, $result->id);
    }

    /**
     * Test getActivityById returns null for non-existent activity.
     */
    public function test_get_activity_by_id_returns_null_for_non_existent_activity(): void
    {
        $result = $this->service->getActivityById(99999, onlyPublished: true);

        $this->assertNull($result);
    }
}

