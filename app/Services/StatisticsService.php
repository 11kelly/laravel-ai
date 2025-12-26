<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Services;

use App\Contracts\StatisticsServiceInterface;
use App\DTOs\ActivityRanking;
use App\DTOs\ActivityStats;
use App\DTOs\DateRange;
use App\DTOs\GlobalStats;
use App\Exceptions\ActivityNotFoundException;
use App\Models\Activity;
use App\Models\Booking;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StatisticsService implements StatisticsServiceInterface
{
    public function getActivityStats(int $activityId): ActivityStats
    {
        $activity = Activity::find($activityId);

        if (!$activity) {
            throw new ActivityNotFoundException($activityId);
        }

        $stats = Booking::where('activity_id', $activityId)
            ->selectRaw('
                COUNT(*) as total_bookings,
                COUNT(CASE WHEN status = "confirmed" THEN 1 END) as confirmed_bookings,
                COUNT(CASE WHEN status = "cancelled" THEN 1 END) as cancelled_bookings,
                SUM(CASE WHEN status = "confirmed" THEN ? ELSE 0 END) as revenue
            ', [$activity->price])
            ->first();

        $occupancyRate = $activity->capacity > 0
            ? (($activity->capacity - $activity->available_slots) / $activity->capacity) * 100
            : 0;

        return new ActivityStats(
            totalBookings: $stats->total_bookings,
            confirmedBookings: $stats->confirmed_bookings,
            cancelledBookings: $stats->cancelled_bookings,
            occupancyRate: round($occupancyRate, 2),
            revenue: (float) $stats->revenue
        );
    }

    public function getGlobalStats(DateRange $range): GlobalStats
    {
        $stats = DB::table('activities')
            ->leftJoin('bookings', 'activities.id', '=', 'bookings.activity_id')
            ->whereBetween('activities.created_at', [$range->startDate, $range->endDate])
            ->selectRaw('
                COUNT(DISTINCT activities.id) as total_activities,
                COUNT(DISTINCT CASE WHEN activities.status = "published" THEN activities.id END) as published_activities,
                COUNT(bookings.id) as total_bookings,
                COUNT(CASE WHEN bookings.status = "confirmed" THEN 1 END) as confirmed_bookings,
                SUM(CASE WHEN bookings.status = "confirmed" THEN activities.price ELSE 0 END) as revenue
            ')
            ->first();

        return new GlobalStats(
            totalActivities: $stats->total_activities,
            publishedActivities: $stats->published_activities,
            totalBookings: $stats->total_bookings,
            confirmedBookings: $stats->confirmed_bookings,
            totalRevenue: (float) $stats->revenue
        );
    }

    public function getPopularActivities(DateRange $range, int $limit): Collection
    {
        $activities = Activity::select([
                'activities.id',
                'activities.title',
                DB::raw('COUNT(bookings.id) as booking_count')
            ])
            ->leftJoin('bookings', function ($join) use ($range) {
                $join->on('activities.id', '=', 'bookings.activity_id')
                     ->whereBetween('bookings.created_at', [$range->startDate, $range->endDate])
                     ->where('bookings.status', 'confirmed');
            })
            ->where('activities.status', 'published')
            ->groupBy('activities.id', 'activities.title')
            ->orderBy('booking_count', 'desc')
            ->orderBy('activities.created_at', 'desc')
            ->limit($limit)
            ->get();

        $rankings = collect();
        $rank = 1;

        foreach ($activities as $activity) {
            $rankings->push(new ActivityRanking(
                activityId: $activity->id,
                activityTitle: $activity->title,
                bookingCount: $activity->booking_count,
                rank: $rank++
            ));
        }

        return $rankings;
    }
}
