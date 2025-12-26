<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Activity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = fake()->dateTimeBetween('+1 day', '+30 days');
        $endTime = fake()->dateTimeBetween($startTime, '+31 days');

        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(3),
            'short_description' => fake()->sentence(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'location' => fake()->address(),
            'max_participants' => fake()->numberBetween(0, 100),
            'current_participants' => 0,
            'image_path' => null,
            'status' => 'draft',
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the activity is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    /**
     * Indicate that the activity is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Indicate that the activity is upcoming (start_time in the future).
     */
    public function upcoming(): static
    {
        return $this->state(function (array $attributes) {
            $startTime = fake()->dateTimeBetween('+1 day', '+30 days');
            $endTime = fake()->dateTimeBetween($startTime, '+31 days');

            return [
                'start_time' => $startTime,
                'end_time' => $endTime,
            ];
        });
    }

    /**
     * Indicate that the activity is ongoing (start_time <= now <= end_time).
     */
    public function ongoing(): static
    {
        return $this->state(function (array $attributes) {
            $startTime = fake()->dateTimeBetween('-7 days', 'now');
            $endTime = fake()->dateTimeBetween('now', '+7 days');

            return [
                'start_time' => $startTime,
                'end_time' => $endTime,
            ];
        });
    }

    /**
     * Indicate that the activity has ended (end_time < now).
     */
    public function ended(): static
    {
        return $this->state(function (array $attributes) {
            $endTime = fake()->dateTimeBetween('-30 days', '-1 day');
            $startTime = fake()->dateTimeBetween('-60 days', $endTime);

            return [
                'start_time' => $startTime,
                'end_time' => $endTime,
            ];
        });
    }

    /**
     * Indicate that the activity has no participant limit.
     */
    public function unlimited(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_participants' => 0,
        ]);
    }

    /**
     * Set the current participants count.
     */
    public function withParticipants(int $count): static
    {
        return $this->state(function (array $attributes) use ($count) {
            return [
                'current_participants' => $count,
                'max_participants' => max($attributes['max_participants'] ?? 0, $count),
            ];
        });
    }

    /**
     * Set the activity as full (current_participants = max_participants).
     */
    public function full(): static
    {
        return $this->state(function (array $attributes) {
            // If max_participants is already set and > 0, use it
            // Otherwise, generate a random number between 1 and 100
            $maxParticipants = isset($attributes['max_participants']) && $attributes['max_participants'] > 0
                ? $attributes['max_participants']
                : fake()->numberBetween(1, 100);

            return [
                'max_participants' => $maxParticipants,
                'current_participants' => $maxParticipants,
            ];
        });
    }
}

