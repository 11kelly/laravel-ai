<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Booking
 */
final class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event' => [
                'id' => $this->event->id,
                'title' => $this->event->title,
                'slug' => $this->event->slug,
                'start_time' => $this->event->start_time->toIso8601String(),
                'end_time' => $this->event->end_time->toIso8601String(),
                'location' => $this->event->location,
                'cover_image_url' => $this->event->cover_image_url,
            ],
            'status' => $this->status->value,
            'participants_count' => $this->participants_count,
            'notes' => $this->notes,
            'booked_at' => $this->created_at->toIso8601String(),
            'can_cancel' => $this->canBeCancelled(),
        ];
    }
}

