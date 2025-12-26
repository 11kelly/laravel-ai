<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class ActivityExpiredException extends Exception
{
    public function __construct(int $activityId)
    {
        parent::__construct("Activity with ID {$activityId} has expired");
    }
}
