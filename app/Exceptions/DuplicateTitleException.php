<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class DuplicateTitleException extends Exception
{
    public function __construct(string $title)
    {
        parent::__construct("Activity with title '{$title}' already exists");
    }
}
