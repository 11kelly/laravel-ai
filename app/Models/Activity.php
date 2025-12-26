<?php

declare(strict_types=1);

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'images',
        'start_at',
        'end_at',
        'capacity',
        'remaining_spots',
        'status',
    ];

    protected $casts = [
        'images' => 'array',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'capacity' => 'integer',
        'remaining_spots' => 'integer',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}

