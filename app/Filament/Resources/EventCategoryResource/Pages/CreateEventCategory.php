<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Resources\EventCategoryResource\Pages;

use App\Filament\Resources\EventCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEventCategory extends CreateRecord
{
    protected static string $resource = EventCategoryResource::class;
}

