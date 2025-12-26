<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Activity extends Model
{
    protected $fillable = [
        'title',
        'summary',
        'description',
        'starts_at',
        'ends_at',
        'timezone',
        'location',
        'capacity',
        'booked_count',
        'status',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(ActivityImage::class)->orderBy('sort_order');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', '=', 'published');
    }

    public function scopePubliclyVisible(Builder $query, ?Carbon $now = null): void
    {
        $now ??= now();

        $query
            ->where('status', '=', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', $now);
    }

    public function isPubliclyVisible(?Carbon $now = null): bool
    {
        $now ??= now();

        if ($this->status !== 'published') {
            return false;
        }

        if (! $this->published_at instanceof Carbon) {
            return false;
        }

        return $now->greaterThanOrEqualTo($this->published_at);
    }

    public function isBookable(?Carbon $now = null): bool
    {
        $now ??= now();

        if (! $this->isPubliclyVisible($now)) {
            return false;
        }

        if (! $this->starts_at instanceof Carbon) {
            return false;
        }

        if ($now->greaterThanOrEqualTo($this->starts_at)) {
            return false;
        }

        return $this->remainingCapacity() > 0;
    }

    public function bookingDisabledReason(?Carbon $now = null): ?string
    {
        $now ??= now();

        if ($this->status !== 'published') {
            return '未发布';
        }

        if (! $this->published_at instanceof Carbon || $now->lessThan($this->published_at)) {
            return '未到发布时间';
        }

        if (! $this->starts_at instanceof Carbon || $now->greaterThanOrEqualTo($this->starts_at)) {
            return '活动已开始';
        }

        if ($this->remainingCapacity() <= 0) {
            return '名额已满';
        }

        return null;
    }

    public function remainingCapacity(): int
    {
        return max(0, (int) $this->capacity - (int) $this->booked_count);
    }
}


