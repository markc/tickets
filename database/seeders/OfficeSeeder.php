<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $offices = [
            [
                'name' => 'Technical Support',
                'description' => 'Handle all technical issues and software problems',
                'is_internal' => false,
            ],
            [
                'name' => 'Sales',
                'description' => 'Handle sales inquiries and billing issues',
                'is_internal' => false,
            ],
            [
                'name' => 'Customer Service',
                'description' => 'General customer service and account support',
                'is_internal' => false,
            ],
            [
                'name' => 'Development Team',
                'description' => 'Internal development team for escalated technical issues',
                'is_internal' => true,
            ],
        ];

        foreach ($offices as $office) {
            Office::firstOrCreate(
                ['name' => $office['name']],
                [
                    'description' => $office['description'],
                    'is_internal' => $office['is_internal'],
                ]
            );
        }
    }
}
