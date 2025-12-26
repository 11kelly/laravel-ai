<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\DTOs;

class CreateBookingRequest
{
    public function __construct(
        public readonly int $activityId,
        public readonly int $userId,
        public readonly array $contactInfo,
        public readonly ?string $specialRequirements = null
    ) {
        if ($activityId < 1) {
            throw new \InvalidArgumentException('Activity ID must be greater than 0');
        }

        if ($userId < 1) {
            throw new \InvalidArgumentException('User ID must be greater than 0');
        }

        if (empty($contactInfo)) {
            throw new \InvalidArgumentException('Contact info is required');
        }
    }
}
