<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => \App\Models\User::factory(),
            'activity_id' => \App\Models\Activity::factory(),
            'status' => 'pending',
            'booking_code' => 'BK-' . strtoupper(\Illuminate\Support\Str::random(8)),
            'participant_info' => [
                'name' => $this->faker->name(),
                'phone' => $this->faker->phoneNumber(),
            ],
            'ticket_quantity' => 1,
        ];
    }
}
