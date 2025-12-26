<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Booking Status Enum
 * 
 * Represents the lifecycle states of a booking
 */
enum BookingStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';

    /**
     * Get label for display
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => '待审核',
            self::CONFIRMED => '已确认',
            self::CANCELLED => '已取消',
            self::COMPLETED => '已完成',
        };
    }

    /**
     * Get color for display
     */
    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::CONFIRMED => 'success',
            self::CANCELLED => 'danger',
            self::COMPLETED => 'info',
        };
    }

    /**
     * Check if booking can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this, [self::PENDING, self::CONFIRMED]);
    }

    /**
     * Get active statuses (not cancelled or completed)
     */
    public static function activeStatuses(): array
    {
        return [self::PENDING, self::CONFIRMED];
    }
}

