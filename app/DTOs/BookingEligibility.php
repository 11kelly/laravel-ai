<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\DTOs;

class BookingEligibility
{
    public function __construct(
        public readonly bool $isEligible,
        public readonly ?string $reason = null
    ) {}

    public static function eligible(): self
    {
        return new self(true);
    }

    public static function ineligible(string $reason): self
    {
        return new self(false, $reason);
    }
}
