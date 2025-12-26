<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'participants_count',
        'status',
        'notes',
        'approved_by',
        'approved_at',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
    ];

    protected $casts = [
        'participants_count' => 'integer',
        'approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'status' => BookingStatus::class,
    ];

    /**
     * Get the event
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the admin who approved the booking
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if booking can be cancelled
     */
    public function canBeCancelled(): bool
    {
        if (!$this->status->canBeCancelled()) {
            return false;
        }

        if ($this->event->start_time->isPast()) {
            return false;
        }

        return true;
    }
    
    /**
     * Check if booking is pending
     */
    public function isPending(): bool
    {
        return $this->status === BookingStatus::PENDING;
    }
    
    /**
     * Check if booking is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === BookingStatus::CONFIRMED;
    }

    /**
     * Scope for pending bookings
     */
    public function scopePending($query)
    {
        return $query->where('status', BookingStatus::PENDING);
    }

    /**
     * Scope for confirmed bookings
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', BookingStatus::CONFIRMED);
    }

    /**
     * Scope for cancelled bookings
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', BookingStatus::CANCELLED);
    }

    /**
     * Scope for completed bookings
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', BookingStatus::COMPLETED);
    }
}

