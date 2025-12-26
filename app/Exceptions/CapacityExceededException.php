<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class CapacityExceededException extends Exception
{
    public function __construct(int $capacity)
    {
        parent::__construct("Activity capacity cannot exceed {$capacity}");
    }
}
