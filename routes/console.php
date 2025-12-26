<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule: Complete passed events and their bookings (runs hourly)
Schedule::command('events:complete-passed')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule: Check booking consistency (runs daily at 3 AM)
Schedule::command('bookings:check-consistency --fix')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->runInBackground();
