<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'title',
        'description',
        'cover_image',
        'start_time',
        'end_time',
        'capacity',
        'reserved_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'capacity' => 'integer',
            'reserved_count' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Admin, $this>
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * @return HasMany<Reservation, $this>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Scope: only published activities.
     *
     * @param Builder<Activity> $query
     * @return Builder<Activity>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope: only upcoming activities.
     *
     * @param Builder<Activity> $query
     * @return Builder<Activity>
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_time', '>', now());
    }

    /**
     * Scope: activities that haven't ended yet.
     *
     * @param Builder<Activity> $query
     * @return Builder<Activity>
     */
    public function scopeNotEnded(Builder $query): Builder
    {
        return $query->where('end_time', '>', now());
    }

    /**
     * Check if activity is reservable.
     */
    public function isReservable(): bool
    {
        return $this->status === 'published'
            && $this->start_time->isFuture()
            && $this->getRemainingCapacity() > 0;
    }

    /**
     * Get remaining capacity.
     */
    public function getRemainingCapacity(): int
    {
        if ($this->capacity === 0) {
            return PHP_INT_MAX; // Unlimited
        }

        return max(0, $this->capacity - $this->reserved_count);
    }

    /**
     * Check if activity has started.
     */
    public function hasStarted(): bool
    {
        return $this->start_time->isPast();
    }

    /**
     * Check if activity has ended.
     */
    public function hasEnded(): bool
    {
        return $this->end_time->isPast();
    }

    /**
     * Get cover image URL.
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        if (! $this->cover_image) {
            return null;
        }

        return asset('storage/' . $this->cover_image);
    }

    /**
     * Get sanitized description (XSS protection).
     * Only allow safe HTML tags from RichEditor.
     */
    public function getSafeDescriptionAttribute(): ?string
    {
        if (! $this->description) {
            return null;
        }

        // Allow only safe HTML tags
        $allowedTags = '<p><br><strong><b><em><i><u><s><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><a><img>';

        $cleaned = strip_tags($this->description, $allowedTags);

        // Remove any javascript: or data: URLs from href and src attributes
        $cleaned = preg_replace('/href\s*=\s*["\']?\s*(javascript|data):/i', 'href="#blocked:', $cleaned);
        $cleaned = preg_replace('/src\s*=\s*["\']?\s*(javascript|data):/i', 'src="#blocked:', $cleaned);

        // Remove on* event handlers
        $cleaned = preg_replace('/\s+on\w+\s*=\s*["\'][^"\']*["\']/i', '', $cleaned);
        $cleaned = preg_replace('/\s+on\w+\s*=\s*[^\s>]*/i', '', $cleaned);

        return $cleaned;
    }
}

