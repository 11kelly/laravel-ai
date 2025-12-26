<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\DTOs;

use Carbon\Carbon;

class ActivityFilter
{
    public function __construct(
        public readonly ?string $status = null,
        public readonly ?Carbon $startDate = null,
        public readonly ?Carbon $endDate = null,
        public readonly ?string $search = null
    ) {
        if ($status !== null && !in_array($status, ['draft', 'published', 'cancelled', 'completed'])) {
            throw new \InvalidArgumentException('Invalid status value');
        }

        if ($startDate !== null && $endDate !== null && $endDate->isBefore($startDate)) {
            throw new \InvalidArgumentException('End date must be after start date');
        }
    }
}
