<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SavedSearch>
 */
class SavedSearchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'search_params' => [
                'q' => fake()->words(2, true),
                'type' => fake()->randomElement(['all', 'tickets', 'faqs']),
                'status' => fake()->randomElements(['Open', 'In Progress', 'Resolved'], rand(1, 2)),
                'priority' => fake()->randomElements(['Low', 'Medium', 'High'], rand(1, 2)),
            ],
            'user_id' => User::factory(),
            'is_public' => fake()->boolean(30), // 30% chance of being public
            'usage_count' => fake()->numberBetween(0, 50),
            'last_used_at' => fake()->optional(0.8)->dateTimeThisMonth(),
        ];
    }

    /**
     * Indicate that the saved search is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Indicate that the saved search is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    /**
     * Indicate that the saved search has been used frequently.
     */
    public function frequentlyUsed(): static
    {
        return $this->state(fn (array $attributes) => [
            'usage_count' => fake()->numberBetween(20, 100),
            'last_used_at' => fake()->dateTimeThisWeek(),
        ]);
    }
}
