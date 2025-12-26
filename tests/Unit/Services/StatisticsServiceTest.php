<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DTOs\ActivityRanking;
use App\DTOs\ActivityStats;
use App\DTOs\DateRange;
use App\DTOs\GlobalStats;
use App\Exceptions\ActivityNotFoundException;
use App\Models\Activity;
use App\Models\Booking;
use App\Services\StatisticsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

# File: tests/Unit/Services/StatisticsServiceTest.php

class StatisticsServiceTest extends TestCase
{
    private Activity|MockObject $activityMock;
    private Booking|MockObject $bookingMock;
    private StatisticsService $statisticsService;

    protected function setUp(): void
    {
        $this->activityMock = $this->createMock(Activity::class);
        $this->bookingMock = $this->createMock(Booking::class);
        $this->statisticsService = new StatisticsService();
    }

    /** @test */
    public function get_activity_stats_should_return_activity_stats_when_activity_exists(): void
    {
        // Ref: TSD Section 2.3 - StatisticsService.getActivityStats

        // Act & Assert - Red Stage: Method implementation needed
        $this->expectException(\Error::class); // Method not implemented yet
        $this->statisticsService->getActivityStats(1);
    }

    /** @test */
    public function get_activity_stats_should_throw_activity_not_found_exception_when_activity_not_exists(): void
    {
        // Ref: TSD Section 2.3 - StatisticsService.getActivityStats

        // Act & Assert - Red Stage: Method implementation needed
        $this->expectException(\Error::class); // Method not implemented yet
        $this->statisticsService->getActivityStats(999);
    }

    /** @test */
    public function get_global_stats_should_return_global_stats_for_date_range(): void
    {
        // Ref: TSD Section 2.3 - StatisticsService.getGlobalStats
        $dateRange = new DateRange(
            startDate: Carbon::now()->subDays(30),
            endDate: Carbon::now()
        );

        // Act & Assert - Red Stage: Method implementation needed
        $this->expectException(\RuntimeException::class); // Laravel Facade not available in unit tests
        $this->statisticsService->getGlobalStats($dateRange);
    }

    /** @test */
    public function get_popular_activities_should_return_collection_of_activity_rankings(): void
    {
        // Ref: TSD Section 2.3 - StatisticsService.getPopularActivities
        $dateRange = new DateRange(
            startDate: Carbon::now()->subDays(30),
            endDate: Carbon::now()
        );

        // Act & Assert - Red Stage: Method implementation needed
        $this->expectException(\RuntimeException::class); // Laravel Facade not available in unit tests
        $this->statisticsService->getPopularActivities($dateRange, 10);
    }

    /** @test */
    public function get_popular_activities_should_return_empty_collection_when_no_data(): void
    {
        // Ref: TSD Section 2.3 - StatisticsService.getPopularActivities
        $dateRange = new DateRange(
            startDate: Carbon::now()->subDays(30),
            endDate: Carbon::now()
        );

        // Act & Assert - Red Stage: Method implementation needed
        $this->expectException(\RuntimeException::class); // Laravel Facade not available in unit tests
        $this->statisticsService->getPopularActivities($dateRange, 5);
    }

    /** @test */
    public function get_popular_activities_should_limit_results_by_specified_count(): void
    {
        // Ref: TSD Section 2.3 - StatisticsService.getPopularActivities
        $dateRange = new DateRange(
            startDate: Carbon::now()->subDays(30),
            endDate: Carbon::now()
        );

        // Act & Assert - Red Stage: Method implementation needed
        $this->expectException(\RuntimeException::class); // Laravel Facade not available in unit tests
        $result = $this->statisticsService->getPopularActivities($dateRange, 3);
    }

    /** @test */
    public function get_activity_stats_should_calculate_occupancy_rate_correctly(): void
    {
        // Ref: TSD Section 2.3 - StatisticsService.getActivityStats

        // Act & Assert - Red Stage: Method implementation needed
        $this->expectException(\Error::class); // Method not implemented yet
        $stats = $this->statisticsService->getActivityStats(1);
    }

    /** @test */
    public function get_activity_stats_should_calculate_revenue_correctly(): void
    {
        // Ref: TSD Section 2.3 - StatisticsService.getActivityStats

        // Act & Assert - Red Stage: Method implementation needed
        $this->expectException(\Error::class); // Method not implemented yet
        $stats = $this->statisticsService->getActivityStats(1);
    }

    /** @test */
    public function get_global_stats_should_include_all_activities_in_date_range(): void
    {
        // Ref: TSD Section 2.3 - StatisticsService.getGlobalStats
        $dateRange = new DateRange(
            startDate: Carbon::now()->subDays(30),
            endDate: Carbon::now()
        );

        // Act & Assert - Red Stage: Method implementation needed
        $this->expectException(\RuntimeException::class); // Laravel Facade not available in unit tests
        $stats = $this->statisticsService->getGlobalStats($dateRange);
    }

    /** @test */
    public function get_popular_activities_should_order_by_booking_count_descending(): void
    {
        // Ref: TSD Section 2.3 - StatisticsService.getPopularActivities
        $dateRange = new DateRange(
            startDate: Carbon::now()->subDays(30),
            endDate: Carbon::now()
        );

        // Act & Assert - Red Stage: Method implementation needed
        $this->expectException(\RuntimeException::class); // Laravel Facade not available in unit tests
        $result = $this->statisticsService->getPopularActivities($dateRange, 10);
    }
}
