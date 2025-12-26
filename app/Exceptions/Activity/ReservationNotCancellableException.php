<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Exceptions\Activity;

use Exception;

class ReservationNotCancellableException extends Exception
{
    public function __construct(string $message = '此預約無法取消')
    {
        parent::__construct($message);
    }
}

