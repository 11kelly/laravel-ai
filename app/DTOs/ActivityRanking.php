<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\DTOs;

class ActivityRanking
{
    public function __construct(
        public readonly int $activityId,
        public readonly string $activityTitle,
        public readonly int $bookingCount,
        public readonly int $rank
    ) {}
}
