<?php

declare(strict_types=1);

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

namespace Database\Factories;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $capacity = $this->faker->numberBetween(10, 100);
        $startAt = $this->faker->dateTimeBetween('now', '+1 month');
        $endAt = (clone $startAt)->modify('+' . $this->faker->numberBetween(2, 8) . ' hours');

        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraphs(3, true),
            'images' => [
                'https://picsum.photos/seed/' . $this->faker->uuid . '/800/600',
                'https://picsum.photos/seed/' . $this->faker->uuid . '/800/600',
            ],
            'start_at' => $startAt,
            'end_at' => $endAt,
            'capacity' => $capacity,
            'remaining_spots' => $capacity,
            'status' => 'published',
        ];
    }
}
