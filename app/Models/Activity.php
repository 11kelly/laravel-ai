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
use Illuminate\Support\Str;

class Activity extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'cover_image',
        'gallery',
        'start_time',
        'end_time',
        'registration_deadline',
        'location',
        'address',
        'capacity',
        'booked_count',
        'status',
        'is_featured',
        'created_by',
    ];

    protected $casts = [
        'gallery' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'registration_deadline' => 'datetime',
        'is_featured' => 'boolean',
        'capacity' => 'integer',
        'booked_count' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Activity $activity): void {
            if (empty($activity->slug)) {
                $activity->slug = Str::slug($activity->title) . '-' . Str::random(6);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now());
    }

    public function scopeOngoing($query)
    {
        return $query->where('start_time', '<=', now())
            ->where('end_time', '>=', now());
    }

    public function scopeEnded($query)
    {
        return $query->where('end_time', '<', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->status === 'published'
            && $this->booked_count < $this->capacity
            && ($this->registration_deadline === null || $this->registration_deadline > now())
            && $this->start_time > now();
    }

    public function getRemainingCapacityAttribute(): int
    {
        return max(0, $this->capacity - $this->booked_count);
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->end_time < now()) {
            return '已结束';
        }
        if ($this->start_time <= now() && $this->end_time >= now()) {
            return '进行中';
        }
        if ($this->booked_count >= $this->capacity) {
            return '已满';
        }
        if ($this->registration_deadline && $this->registration_deadline < now()) {
            return '报名截止';
        }

        return '可预约';
    }
}

