<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $service = new \App\Services\EmailTemplateService;
        $service->createDefaultTemplates();
    }
}
