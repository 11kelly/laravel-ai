<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test temporal_status attribute for published upcoming activity.
     */
    public function test_temporal_status_returns_upcoming_for_published_future_activity(): void
    {
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->create();

        $this->assertEquals('upcoming', $activity->temporal_status);
    }

    /**
     * Test temporal_status attribute for published ongoing activity.
     */
    public function test_temporal_status_returns_ongoing_for_published_current_activity(): void
    {
        $activity = Activity::factory()
            ->published()
            ->ongoing()
            ->create();

        $this->assertEquals('ongoing', $activity->temporal_status);
    }

    /**
     * Test temporal_status attribute for published ended activity.
     */
    public function test_temporal_status_returns_ended_for_published_past_activity(): void
    {
        $activity = Activity::factory()
            ->published()
            ->ended()
            ->create();

        $this->assertEquals('ended', $activity->temporal_status);
    }

    /**
     * Test temporal_status returns null for non-published activity.
     */
    public function test_temporal_status_returns_null_for_draft_activity(): void
    {
        $activity = Activity::factory()
            ->upcoming()
            ->create(['status' => 'draft']);

        $this->assertNull($activity->temporal_status);
    }

    /**
     * Test temporal_status returns null for cancelled activity.
     */
    public function test_temporal_status_returns_null_for_cancelled_activity(): void
    {
        $activity = Activity::factory()
            ->cancelled()
            ->create();

        $this->assertNull($activity->temporal_status);
    }

    /**
     * Test isAvailableForBooking returns true for available activity.
     */
    public function test_is_available_for_booking_returns_true_for_available_activity(): void
    {
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->create([
                'max_participants' => 10,
                'current_participants' => 5,
            ]);

        $this->assertTrue($activity->isAvailableForBooking());
    }

    /**
     * Test isAvailableForBooking returns false for draft activity.
     */
    public function test_is_available_for_booking_returns_false_for_draft_activity(): void
    {
        $activity = Activity::factory()
            ->upcoming()
            ->create(['status' => 'draft']);

        $this->assertFalse($activity->isAvailableForBooking());
    }

    /**
     * Test isAvailableForBooking returns false for cancelled activity.
     */
    public function test_is_available_for_booking_returns_false_for_cancelled_activity(): void
    {
        $activity = Activity::factory()
            ->cancelled()
            ->upcoming()
            ->create();

        $this->assertFalse($activity->isAvailableForBooking());
    }

    /**
     * Test isAvailableForBooking returns false for started activity.
     */
    public function test_is_available_for_booking_returns_false_for_started_activity(): void
    {
        $activity = Activity::factory()
            ->published()
            ->ongoing()
            ->create();

        $this->assertFalse($activity->isAvailableForBooking());
    }

    /**
     * Test isAvailableForBooking returns false for ended activity.
     */
    public function test_is_available_for_booking_returns_false_for_ended_activity(): void
    {
        $activity = Activity::factory()
            ->published()
            ->ended()
            ->create();

        $this->assertFalse($activity->isAvailableForBooking());
    }

    /**
     * Test isAvailableForBooking returns false when activity is full.
     */
    public function test_is_available_for_booking_returns_false_when_activity_is_full(): void
    {
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->full()
            ->create();

        $this->assertFalse($activity->isAvailableForBooking());
    }

    /**
     * Test isAvailableForBooking returns true for unlimited activity.
     */
    public function test_is_available_for_booking_returns_true_for_unlimited_activity(): void
    {
        $activity = Activity::factory()
            ->published()
            ->upcoming()
            ->unlimited()
            ->create();

        $this->assertTrue($activity->isAvailableForBooking());
    }

    /**
     * Test image_url attribute returns null when image_path is null.
     */
    public function test_image_url_returns_null_when_image_path_is_null(): void
    {
        $activity = Activity::factory()->create(['image_path' => null]);

        $this->assertNull($activity->image_url);
    }

    /**
     * Test image_url attribute returns correct URL when image_path is set.
     */
    public function test_image_url_returns_correct_url_when_image_path_is_set(): void
    {
        $activity = Activity::factory()->create(['image_path' => 'activities/1/image.jpg']);

        $this->assertStringContainsString('storage/activities/1/image.jpg', $activity->image_url);
    }

    /**
     * Test published scope filters only published activities.
     */
    public function test_published_scope_filters_only_published_activities(): void
    {
        Activity::factory()->count(3)->create(['status' => 'draft']);
        Activity::factory()->count(2)->published()->create();
        Activity::factory()->count(1)->cancelled()->create();

        $published = Activity::published()->get();

        $this->assertCount(2, $published);
        $this->assertTrue($published->every(fn ($activity) => $activity->status === 'published'));
    }

    /**
     * Test temporalStatus scope filters by temporal status.
     */
    public function test_temporal_status_scope_filters_by_temporal_status(): void
    {
        Activity::factory()->count(2)->published()->upcoming()->create();
        Activity::factory()->count(1)->published()->ongoing()->create();
        Activity::factory()->count(1)->published()->ended()->create();

        $upcoming = Activity::published()->temporalStatus('upcoming')->get();
        $this->assertCount(2, $upcoming);

        $ongoing = Activity::published()->temporalStatus('ongoing')->get();
        $this->assertCount(1, $ongoing);

        $ended = Activity::published()->temporalStatus('ended')->get();
        $this->assertCount(1, $ended);
    }

    /**
     * Test creator relationship.
     */
    public function test_creator_relationship(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $activity->creator);
        $this->assertEquals($user->id, $activity->creator->id);
    }

    /**
     * Test bookings relationship.
     */
    public function test_bookings_relationship(): void
    {
        $activity = Activity::factory()->create();
        $activity->bookings()->create([
            'user_id' => User::factory()->create()->id,
            'status' => 'pending',
        ]);

        $this->assertCount(1, $activity->bookings);
    }
}

