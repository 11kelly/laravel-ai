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

class Event extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'image',
        'start_time',
        'end_time',
        'location',
        'max_participants',
        'current_participants',
        'status',
        'booking_deadline',
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
            'booking_deadline' => 'datetime',
            'max_participants' => 'integer',
            'current_participants' => 'integer',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Event $event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->title);
            }
            
            // Auto-fill created_by with current admin user
            if (empty($event->created_by) && auth()->guard('admin')->check()) {
                $event->created_by = auth()->guard('admin')->id();
            }
        });
    }

    /**
     * Get the admin who created this event.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Get the bookings for this event.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Check if the event can be booked.
     */
    public function canBook(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        if ($this->current_participants >= $this->max_participants) {
            return false;
        }

        if ($this->booking_deadline && now()->isAfter($this->booking_deadline)) {
            return false;
        }

        if (now()->isAfter($this->start_time)) {
            return false;
        }

        return true;
    }

    /**
     * Scope a query to only include published events.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include upcoming events.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now());
    }
}
