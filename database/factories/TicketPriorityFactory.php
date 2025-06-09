<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketPriority>
 */
class TicketPriorityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Low', 'Medium', 'High', 'Critical']),
            'level' => fake()->numberBetween(1, 4),
            'color' => fake()->hexColor(),
            'is_default' => false,
        ];
    }

    /**
     * Indicate that this is the default priority.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
            'name' => 'Medium',
            'level' => 2,
        ]);
    }

    /**
     * Indicate that this is a high priority.
     */
    public function high(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'High',
            'level' => 3,
        ]);
    }

    /**
     * Indicate that this is a low priority.
     */
    public function low(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Low',
            'level' => 1,
        ]);
    }
}
