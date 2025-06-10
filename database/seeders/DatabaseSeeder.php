<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TicketStatusSeeder::class,
            TicketPrioritySeeder::class,
            OfficeSeeder::class,
            AdminUserSeeder::class,
            FAQSeeder::class,
            EmailTemplateSeeder::class,
            DocumentationSeeder::class,
        ]);
    }
}
