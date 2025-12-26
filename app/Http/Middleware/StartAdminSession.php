<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Session\Middleware\StartSession;

class StartAdminSession extends StartSession
{
    /**
     * Get the name of the session cookie.
     *
     * @return string
     */
    protected function getSessionCookieName(): string
    {
        return config('session.cookie') . '_admin';
    }

    /**
     * Get the session path.
     *
     * @return string
     */
    protected function getSessionPath(): string
    {
        return '/admin';
    }
}

