<?php

namespace Database\Seeders;

use App\Models\Documentation;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocumentationSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        $docs = [
            [
                'title' => 'TIKM Documentation',
                'slug' => 'index',
                'file_path' => 'index.md',
                'description' => 'Complete guide to the TIKM customer support system',
                'category' => 'overview',
                'order' => 1,
                'version' => '1.0',
                'content' => file_get_contents(base_path('docs/index.md')),
                'is_published' => true,
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
            ],
            [
                'title' => 'Quick Start Guide',
                'slug' => 'quick-start',
                'file_path' => 'user/quick-start.md',
                'description' => 'Get TIKM running in 5 minutes',
                'category' => 'user',
                'order' => 1,
                'version' => '1.0',
                'content' => file_get_contents(base_path('docs/user/quick-start.md')),
                'is_published' => true,
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
            ],
            [
                'title' => 'User Guide',
                'slug' => 'user-guide',
                'file_path' => 'user/user-guide.md',
                'description' => 'Complete guide for customers, agents, and administrators',
                'category' => 'user',
                'order' => 2,
                'version' => '1.0',
                'content' => file_get_contents(base_path('docs/user/user-guide.md')),
                'is_published' => true,
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
            ],
            [
                'title' => 'FAQ System Guide',
                'slug' => 'faq-system',
                'file_path' => 'user/faq-system.md',
                'description' => 'Self-service knowledge base usage and management',
                'category' => 'user',
                'order' => 3,
                'version' => '1.0',
                'content' => file_get_contents(base_path('docs/user/faq-system.md')),
                'is_published' => true,
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
            ],
        ];

        foreach ($docs as $docData) {
            Documentation::create($docData);
        }
    }
}
