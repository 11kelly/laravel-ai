<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\DTOs;

use Carbon\Carbon;

class DateRange
{
    public function __construct(
        public readonly Carbon $startDate,
        public readonly Carbon $endDate
    ) {
        if ($endDate->isBefore($startDate)) {
            throw new \InvalidArgumentException('End date must be after start date');
        }
    }
}
