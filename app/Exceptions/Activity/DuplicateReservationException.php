<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Exceptions\Activity;

use Exception;

class DuplicateReservationException extends Exception
{
    public function __construct(string $message = '您已預約過此活動')
    {
        parent::__construct($message);
    }
}

