<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_code' => Booking::generateBookingCode(),
            'user_id' => User::factory(),
            'activity_id' => Activity::factory(),
            'participants' => fake()->numberBetween(1, 5),
            'status' => 'confirmed',
            'remarks' => fake()->optional()->sentence(),
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ];
    }

    /**
     * Indicate that the booking is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);
    }

    /**
     * Indicate that the booking is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => fake()->optional()->sentence(),
        ]);
    }

    /**
     * Indicate that the booking is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the booking is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Set a specific number of participants.
     */
    public function withParticipants(int $count): static
    {
        return $this->state(fn (array $attributes) => [
            'participants' => $count,
        ]);
    }

    /**
     * Set remarks for the booking.
     */
    public function withRemarks(string $remarks): static
    {
        return $this->state(fn (array $attributes) => [
            'remarks' => $remarks,
        ]);
    }

    /**
     * Set the user for the booking.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Set the activity for the booking.
     */
    public function forActivity(Activity $activity): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_id' => $activity->id,
        ]);
    }
}

