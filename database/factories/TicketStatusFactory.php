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
            'is_default' => false,
            'is_closed' => fake()->boolean(25),
        ];
    }

    /**
     * Indicate that this is the default status.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
            'name' => 'Open',
        ]);
    }

    /**
     * Indicate that this is a closed status.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_closed' => true,
            'name' => fake()->randomElement(['Resolved', 'Closed']),
        ]);
    }
}
