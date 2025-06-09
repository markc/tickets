<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketStatus>
 */
class TicketStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Open', 'In Progress', 'Resolved', 'Closed']),
            'color' => fake()->hexColor(),
        ];
    }

    /**
     * Indicate that this is an open status.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Open',
        ]);
    }

    /**
     * Indicate that this is a closed status.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement(['Resolved', 'Closed']),
        ]);
    }
}
