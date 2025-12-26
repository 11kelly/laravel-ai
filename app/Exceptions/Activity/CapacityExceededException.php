<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Exceptions\Activity;

use Exception;

class CapacityExceededException extends Exception
{
    public function __construct(string $message = '活動名額已滿')
    {
        parent::__construct($message);
    }
}

