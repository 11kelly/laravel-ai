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
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);
        $startTime = fake()->dateTimeBetween('+1 week', '+1 month');
        $endTime = (clone $startTime)->modify('+' . rand(2, 8) . ' hours');

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(6),
            'description' => fake()->paragraph(),
            'content' => fake()->paragraphs(3, true),
            'cover_image' => null,
            'gallery' => null,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'registration_deadline' => (clone $startTime)->modify('-1 day'),
            'location' => fake()->city(),
            'address' => fake()->address(),
            'capacity' => fake()->numberBetween(20, 200),
            'booked_count' => 0,
            'status' => 'published',
            'is_featured' => fake()->boolean(20),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the activity is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
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
     * Indicate that the activity is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the activity is not featured.
     */
    public function notFeatured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => false,
        ]);
    }

    /**
     * Indicate that the activity is upcoming (starts in the future).
     */
    public function upcoming(): static
    {
        return $this->state(function (array $attributes) {
            $startTime = now()->addDays(rand(7, 30));
            $endTime = (clone $startTime)->addHours(rand(2, 8));

            return [
                'start_time' => $startTime,
                'end_time' => $endTime,
                'registration_deadline' => (clone $startTime)->subDay(),
            ];
        });
    }

    /**
     * Indicate that the activity is ongoing (currently in progress).
     */
    public function ongoing(): static
    {
        return $this->state(function (array $attributes) {
            $startTime = now()->subHours(rand(1, 3));
            $endTime = now()->addHours(rand(2, 6));

            return [
                'start_time' => $startTime,
                'end_time' => $endTime,
                'registration_deadline' => (clone $startTime)->subDay(),
            ];
        });
    }

    /**
     * Indicate that the activity has ended.
     */
    public function ended(): static
    {
        return $this->state(function (array $attributes) {
            $startTime = now()->subDays(rand(2, 7));
            $endTime = (clone $startTime)->addHours(rand(2, 8));

            return [
                'start_time' => $startTime,
                'end_time' => $endTime,
                'registration_deadline' => (clone $startTime)->subDay(),
            ];
        });
    }

    /**
     * Indicate that the activity is at full capacity.
     */
    public function full(): static
    {
        return $this->state(fn (array $attributes) => [
            'booked_count' => $attributes['capacity'] ?? 100,
        ]);
    }

    /**
     * Indicate that the activity has no registration deadline.
     */
    public function noDeadline(): static
    {
        return $this->state(fn (array $attributes) => [
            'registration_deadline' => null,
        ]);
    }

    /**
     * Indicate that the registration deadline has passed.
     */
    public function deadlinePassed(): static
    {
        return $this->state(fn (array $attributes) => [
            'registration_deadline' => now()->subHours(rand(1, 24)),
        ]);
    }
}

