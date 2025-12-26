<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\DTOs;

class ActivityStats
{
    public function __construct(
        public readonly int $totalBookings,
        public readonly int $confirmedBookings,
        public readonly int $cancelledBookings,
        public readonly float $occupancyRate,
        public readonly float $revenue
    ) {}
}
