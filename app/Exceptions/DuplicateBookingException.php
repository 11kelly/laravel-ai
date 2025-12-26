<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class DuplicateBookingException extends Exception
{
    public function __construct(int $activityId, int $userId)
    {
        parent::__construct("User {$userId} already has a booking for activity {$activityId}");
    }
}
