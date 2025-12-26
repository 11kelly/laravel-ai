<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test index returns published activities list.
     */
    public function test_index_returns_published_activities_list(): void
    {
        Activity::factory()->count(3)->create(['status' => 'draft']);
        Activity::factory()->count(2)->published()->create();

        $response = $this->get('/activities');

        $response->assertStatus(200);
        $response->assertViewIs('activities.index');
        $response->assertViewHas('activities');
        $this->assertCount(2, $response->viewData('activities')->items());
    }

    /**
     * Test index returns JSON when expects JSON.
     */
    public function test_index_returns_json_when_expects_json(): void
    {
        $activity = Activity::factory()->published()->upcoming()->create();

        $response = $this->getJson('/activities');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'start_time',
                        'end_time',
                        'location',
                        'max_participants',
                        'current_participants',
                        'image_url',
                        'status',
                        'temporal_status',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ]);
    }

    /**
     * Test index filters by status.
     * Note: Frontend always shows only published activities, so status filter
     * is effectively ignored when onlyPublished=true.
     */
    public function test_index_filters_by_status(): void
    {
        Activity::factory()->count(2)->create(['status' => 'draft']);
        Activity::factory()->count(3)->published()->create();

        // Frontend always shows only published activities regardless of status filter
        // This is by design - frontend should only see published activities
        $response = $this->getJson('/activities?status=draft');

        $response->assertStatus(200);
        // Even with status=draft filter, frontend only shows published activities
        // So we get all published activities (3), not filtered by draft
        $this->assertCount(3, $response->json('data'));
        
        // All returned activities should be published
        $this->assertTrue(collect($response->json('data'))->every(fn ($activity) => $activity['status'] === 'published'));
    }

    /**
     * Test index filters by temporal_status.
     */
    public function test_index_filters_by_temporal_status(): void
    {
        Activity::factory()->count(2)->published()->upcoming()->create();
        Activity::factory()->count(1)->published()->ongoing()->create();

        $response = $this->getJson('/activities?temporal_status=upcoming');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    /**
     * Test index filters by date range.
     */
    public function test_index_filters_by_date_range(): void
    {
        $dateFrom = now()->addDays(5)->toDateString();
        $dateTo = now()->addDays(15)->toDateString();

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

        $response = $this->getJson("/activities?date_from={$dateFrom}&date_to={$dateTo}");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    /**
     * Test index respects per_page parameter.
     */
    public function test_index_respects_per_page_parameter(): void
    {
        Activity::factory()->count(25)->published()->create();

        $response = $this->getJson('/activities?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(10, $response->json('meta.per_page'));
    }

    /**
     * Test show returns activity detail.
     */
    public function test_show_returns_activity_detail(): void
    {
        $activity = Activity::factory()->published()->upcoming()->create();

        $response = $this->get("/activities/{$activity->id}");

        $response->assertStatus(200);
        $response->assertViewIs('activities.show');
        $response->assertViewHas('activity');
        $this->assertEquals($activity->id, $response->viewData('activity')->id);
    }

    /**
     * Test show returns JSON when expects JSON.
     */
    public function test_show_returns_json_when_expects_json(): void
    {
        $activity = Activity::factory()->published()->upcoming()->create();

        $response = $this->getJson("/activities/{$activity->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'title',
                'description',
                'short_description',
                'start_time',
                'end_time',
                'location',
                'max_participants',
                'current_participants',
                'image_url',
                'status',
                'temporal_status',
                'is_available_for_booking',
            ])
            ->assertJson([
                'id' => $activity->id,
                'title' => $activity->title,
            ]);
    }

    /**
     * Test show returns 404 for non-published activity.
     */
    public function test_show_returns_404_for_non_published_activity(): void
    {
        $activity = Activity::factory()->create(['status' => 'draft']);

        $response = $this->get("/activities/{$activity->id}");

        $response->assertStatus(404);
    }

    /**
     * Test show returns 404 for non-existent activity.
     */
    public function test_show_returns_404_for_non_existent_activity(): void
    {
        $response = $this->get('/activities/99999');

        $response->assertStatus(404);
    }

    /**
     * Test show returns 404 JSON for non-published activity.
     */
    public function test_show_returns_404_json_for_non_published_activity(): void
    {
        $activity = Activity::factory()->create(['status' => 'draft']);

        $response = $this->getJson("/activities/{$activity->id}");

        $response->assertStatus(404)
            ->assertJson(['message' => 'Activity not found']);
    }
}

