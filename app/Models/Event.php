<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'cover_image',
        'start_time',
        'end_time',
        'booking_deadline',
        'location',
        'capacity',
        'booked_count',
        'status',
        'booking_rules',
        'organizer_name',
        'organizer_contact',
        'created_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'booking_deadline' => 'datetime',
        'capacity' => 'integer',
        'booked_count' => 'integer',
        'status' => EventStatus::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->title) . '-' . time();
            }
        });
    }

    /**
     * Get the category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'category_id');
    }

    /**
     * Get the creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get event images
     */
    public function images(): HasMany
    {
        return $this->hasMany(EventImage::class)->orderBy('display_order');
    }

    /**
     * Get bookings
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get confirmed bookings
     */
    public function confirmedBookings(): HasMany
    {
        return $this->hasMany(Booking::class)->where('status', 'confirmed');
    }

    /**
     * Check if event is bookable
     */
    public function isBookable(): bool
    {
        if (!$this->status->isBookable()) {
            return false;
        }

        if ($this->start_time->isPast()) {
            return false;
        }

        if ($this->booking_deadline && $this->booking_deadline->isPast()) {
            return false;
        }

        if ($this->booked_count >= $this->capacity) {
            return false;
        }

        return true;
    }

    /**
     * Get remaining capacity
     */
    public function getRemainingCapacity(): int
    {
        return max(0, $this->capacity - $this->booked_count);
    }

    /**
     * Get cover image URL
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        if (!$this->cover_image) {
            return null;
        }

        if (Str::startsWith($this->cover_image, ['http://', 'https://'])) {
            return $this->cover_image;
        }

        return asset('storage/' . $this->cover_image);
    }

    /**
     * Scope for published events
     */
    public function scopePublished($query)
    {
        return $query->where('status', EventStatus::PUBLISHED);
    }

    /**
     * Scope for upcoming events
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now());
    }

    /**
     * Scope for past events
     */
    public function scopePast($query)
    {
        return $query->where('end_time', '<', now());
    }

    /**
     * Scope for ongoing events
     */
    public function scopeOngoing($query)
    {
        return $query->where('start_time', '<=', now())
                    ->where('end_time', '>=', now());
    }

    /**
     * Get max_participants attribute (alias for capacity)
     * 
     * 为了兼容视图中的字段名，提供 max_participants 访问器
     */
    public function getMaxParticipantsAttribute(): ?int
    {
        return $this->capacity;
    }
}

