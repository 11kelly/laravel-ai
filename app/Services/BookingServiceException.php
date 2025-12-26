<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class BookingServiceException extends RuntimeException
{
    public function __construct(string $errorCode)
    {
        parent::__construct($errorCode);
    }

    public function errorCode(): string
    {
        return $this->getMessage();
    }
}


