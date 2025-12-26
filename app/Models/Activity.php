<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'short_description',
        'start_time',
        'end_time',
        'location',
        'max_participants',
        'current_participants',
        'image_path',
        'status',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'max_participants' => 'integer',
            'current_participants' => 'integer',
        ];
    }

    /**
     * Get the creator of the activity.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the bookings for the activity.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the temporal status of the activity.
     */
    public function getTemporalStatusAttribute(): ?string
    {
        if ($this->status !== 'published') {
            return null;
        }

        $now = now();

        if ($this->start_time > $now) {
            return 'upcoming';
        }

        if ($this->start_time <= $now && $this->end_time >= $now) {
            return 'ongoing';
        }

        if ($this->end_time < $now) {
            return 'ended';
        }

        return null;
    }

    /**
     * Check if the activity is available for booking.
     */
    public function isAvailableForBooking(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        if ($this->start_time <= now()) {
            return false;
        }

        if ($this->max_participants > 0 && $this->current_participants >= $this->max_participants) {
            return false;
        }

        return true;
    }

    /**
     * Get the image URL.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return asset('storage/'.$this->image_path);
    }

    /**
     * Scope a query to only include published activities.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to filter by temporal status.
     */
    public function scopeTemporalStatus($query, string $status)
    {
        $now = now();

        return match ($status) {
            'upcoming' => $query->where('start_time', '>', $now),
            'ongoing' => $query->where('start_time', '<=', $now)
                ->where('end_time', '>=', $now),
            'ended' => $query->where('end_time', '<', $now),
            default => $query,
        };
    }
}

