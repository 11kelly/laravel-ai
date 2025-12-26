<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EventImage extends Model
{
    protected $fillable = [
        'event_id',
        'image_url',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    /**
     * Get the event
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get full image URL
     */
    public function getFullUrlAttribute(): string
    {
        if (Str::startsWith($this->image_url, ['http://', 'https://'])) {
            return $this->image_url;
        }

        return asset('storage/' . $this->image_url);
    }
}

