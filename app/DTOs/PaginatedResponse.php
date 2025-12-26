<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\DTOs;

class PaginatedResponse
{
    /**
     * @param array $data
     */
    public function __construct(
        public readonly array $data,
        public readonly int $currentPage,
        public readonly int $perPage,
        public readonly int $total,
        public readonly int $lastPage
    ) {}

    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage;
    }
}
