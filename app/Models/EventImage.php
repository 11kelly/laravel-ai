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
            return $this->addPortToUrl($this->image_url);
        }

        $url = asset('storage/' . $this->image_url);
        return $this->addPortToUrl($url);
    }

    /**
     * Add port 8000 to URL if not already present
     */
    private function addPortToUrl(string $url): string
    {
        // If URL is relative, return as is (shouldn't happen with asset())
        if (!Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        // Parse URL
        $parsed = parse_url($url);
        
        if (!$parsed || !isset($parsed['host'])) {
            return $url;
        }

        // If port is already set, return as is
        if (isset($parsed['port'])) {
            return $url;
        }

        // Rebuild URL with port 8000
        $scheme = $parsed['scheme'] ?? 'http';
        $host = $parsed['host'];
        $port = ':8000';
        $path = $parsed['path'] ?? '';
        $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
        $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';

        return $scheme . '://' . $host . $port . $path . $query . $fragment;
    }
}

