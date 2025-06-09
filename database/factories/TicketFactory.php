<?php

namespace Database\Factories;

use App\Models\Office;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'subject' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'creator_id' => User::factory(),
            'office_id' => Office::factory(),
            'status_id' => TicketStatus::factory(),
            'priority_id' => TicketPriority::factory(),
            'assigned_to_id' => null,
        ];
    }

    /**
     * Indicate that the ticket is assigned to an agent.
     */
    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to_id' => User::factory(['role' => 'agent']),
        ]);
    }

    /**
     * Indicate that the ticket has high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority_id' => TicketPriority::factory(['name' => 'High']),
        ]);
    }

    /**
     * Indicate that the ticket is open.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => TicketStatus::factory(['name' => 'Open']),
        ]);
    }
}
