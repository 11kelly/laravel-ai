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

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'image',
        'price',
        'capacity',
        'available_slots',
        'start_date',
        'end_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'capacity' => 'integer',
        'available_slots' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($activity) {
            if (empty($activity->created_by)) {
                $activity->created_by = \Illuminate\Support\Facades\Auth::id();
            }
        });
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isFull(): bool
    {
        return $this->available_slots <= 0;
    }

    public function hasAvailableSlots(): bool
    {
        return $this->available_slots > 0;
    }

    public function decrementAvailableSlots(): bool
    {
        if (!$this->hasAvailableSlots()) {
            return false;
        }

        return $this->decrement('available_slots') > 0;
    }

    public function incrementAvailableSlots(): bool
    {
        if ($this->available_slots >= $this->capacity) {
            return false;
        }

        return $this->increment('available_slots') > 0;
    }
}
