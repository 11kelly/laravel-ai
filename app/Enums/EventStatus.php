<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Event Status Enum
 * 
 * Represents the lifecycle states of an event
 */
enum EventStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
    case ARCHIVED = 'archived';

    /**
     * Get label for display
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => '草稿',
            self::PUBLISHED => '已发布',
            self::CANCELLED => '已取消',
            self::COMPLETED => '已完成',
            self::ARCHIVED => '已归档',
        };
    }

    /**
     * Get color for display
     */
    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::PUBLISHED => 'success',
            self::CANCELLED => 'danger',
            self::COMPLETED => 'info',
            self::ARCHIVED => 'warning',
        };
    }

    /**
     * Check if event can be booked
     */
    public function isBookable(): bool
    {
        return $this === self::PUBLISHED;
    }
}

