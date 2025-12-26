<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\DTOs;

use Carbon\Carbon;

class BookingFilter
{
    public function __construct(
        public readonly ?string $status = null,
        public readonly ?Carbon $startDate = null,
        public readonly ?Carbon $endDate = null
    ) {
        if ($status !== null && !in_array($status, ['pending', 'confirmed', 'cancelled', 'attended'])) {
            throw new \InvalidArgumentException('Invalid status value');
        }

        if ($startDate !== null && $endDate !== null && $endDate->isBefore($startDate)) {
            throw new \InvalidArgumentException('End date must be after start date');
        }
    }
}
