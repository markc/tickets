<?php

namespace Database\Seeders;

use App\Models\TicketPriority;
use Illuminate\Database\Seeder;

class TicketPrioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $priorities = [
            ['name' => 'Low', 'color' => 'gray'],
            ['name' => 'Medium', 'color' => 'blue'],
            ['name' => 'High', 'color' => 'red'],
            ['name' => 'Urgent', 'color' => 'purple'],
        ];

        foreach ($priorities as $priority) {
            TicketPriority::firstOrCreate(
                ['name' => $priority['name']],
                ['color' => $priority['color']]
            );
        }
    }
}
