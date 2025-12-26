<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\DTOs;

use Carbon\Carbon;

class CreateActivityRequest
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly ?float $price,
        public readonly int $capacity,
        public readonly Carbon $startDate,
        public readonly Carbon $endDate
    ) {
        if (strlen($title) < 1 || strlen($title) > 255) {
            throw new \InvalidArgumentException('Title must be between 1 and 255 characters');
        }

        if (empty($description)) {
            throw new \InvalidArgumentException('Description is required');
        }

        if ($capacity < 1) {
            throw new \InvalidArgumentException('Capacity must be greater than 0');
        }

        if ($price !== null && $price < 0) {
            throw new \InvalidArgumentException('Price must be greater than or equal to 0');
        }

        if ($endDate->isBefore($startDate)) {
            throw new \InvalidArgumentException('End date must be after start date');
        }
    }
}
