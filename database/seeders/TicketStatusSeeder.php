<?php

namespace Database\Seeders;

use App\Models\TicketStatus;
use Illuminate\Database\Seeder;

class TicketStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'Open', 'color' => 'blue'],
            ['name' => 'In Progress', 'color' => 'orange'],
            ['name' => 'On Hold', 'color' => 'yellow'],
            ['name' => 'Closed', 'color' => 'green'],
        ];

        foreach ($statuses as $status) {
            TicketStatus::firstOrCreate(
                ['name' => $status['name']],
                ['color' => $status['color']]
            );
        }
    }
}
