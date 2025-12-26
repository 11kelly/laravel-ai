<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\DTOs;

use Carbon\Carbon;

class UpdateActivityRequest
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?float $price = null,
        public readonly ?int $capacity = null,
        public readonly ?Carbon $startDate = null,
        public readonly ?Carbon $endDate = null,
        public readonly ?string $status = null
    ) {
        if ($title !== null && (strlen($title) < 1 || strlen($title) > 255)) {
            throw new \InvalidArgumentException('Title must be between 1 and 255 characters');
        }

        if ($price !== null && $price < 0) {
            throw new \InvalidArgumentException('Price must be greater than or equal to 0');
        }

        if ($capacity !== null && $capacity < 1) {
            throw new \InvalidArgumentException('Capacity must be greater than 0');
        }

        if ($startDate !== null && $endDate !== null && $endDate->isBefore($startDate)) {
            throw new \InvalidArgumentException('End date must be after start date');
        }

        if ($status !== null && !in_array($status, ['draft', 'published', 'cancelled', 'completed'])) {
            throw new \InvalidArgumentException('Invalid status value');
        }
    }
}
