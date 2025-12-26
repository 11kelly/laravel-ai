<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\DTOs;

class GlobalStats
{
    public function __construct(
        public readonly int $totalActivities,
        public readonly int $publishedActivities,
        public readonly int $totalBookings,
        public readonly int $confirmedBookings,
        public readonly float $totalRevenue
    ) {}
}
