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
            'color' => fake()->hexColor(),
        ];
    }

    /**
     * Indicate that this is a high priority.
     */
    public function high(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'High',
        ]);
    }

    /**
     * Indicate that this is a medium priority.
     */
    public function medium(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Medium',
        ]);
    }

    /**
     * Indicate that this is a low priority.
     */
    public function low(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Low',
        ]);
    }
}
