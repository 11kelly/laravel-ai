<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Exceptions\Activity;

use Exception;

class ActivityNotReservableException extends Exception
{
    public function __construct(string $message = '此活動目前無法預約')
    {
        parent::__construct($message);
    }
}

