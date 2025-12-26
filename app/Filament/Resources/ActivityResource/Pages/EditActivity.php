<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Resources\ActivityResource\Pages;

use App\Filament\Resources\ActivityResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditActivity extends EditRecord
{
    protected static string $resource = ActivityResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        if (($data['status'] ?? null) === 'published') {
            if (empty($data['published_at'])) {
                $data['published_at'] = now();
            }
        } else {
            $data['published_at'] = null;
        }

        if (array_key_exists('capacity', $data)) {
            $minCapacity = max(1, (int) $this->record->booked_count);
            if ((int) $data['capacity'] < $minCapacity) {
                throw ValidationException::withMessages([
                    'capacity' => '容量不能小于当前已预约人数（' . $minCapacity . '）。',
                ]);
            }
        }

        return $data;
    }
}


