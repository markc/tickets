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
            // Overview
            [
                'title' => 'TIKM Documentation Index',
                'slug' => 'index',
                'file_path' => 'index.md',
                'description' => 'Complete documentation index for the TIKM customer support system',
                'category' => 'overview',
                'order' => 1,
                'version' => '1.0',
                'content' => file_get_contents(base_path('docs/index.md')),
                'is_published' => true,
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
            ],
            [
                'title' => 'Ticketing System Overview',
                'slug' => 'ticketing-system',
                'file_path' => 'overview/ticketing-system.md',
                'description' => 'Core ticketing system overview and concepts',
                'category' => 'overview',
                'order' => 2,
                'version' => '1.0',
                'content' => file_get_contents(base_path('docs/overview/ticketing-system.md')),
                'is_published' => true,
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
            ],
            [
                'title' => 'Project README',
                'slug' => 'readme',
                'file_path' => 'overview/readme.md',
                'description' => 'Project introduction and overview',
                'category' => 'overview',
                'order' => 3,
                'version' => '1.0',
                'content' => file_get_contents(base_path('docs/overview/readme.md')),
                'is_published' => true,
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
            ],

            // User Guide
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
                'description' => 'Complete user documentation',
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
                'description' => 'Knowledge base and FAQ usage',
                'category' => 'user',
                'order' => 3,
                'version' => '1.0',
                'content' => file_get_contents(base_path('docs/user/faq-system.md')),
                'is_published' => true,
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
            ],

            // Administration
            [
                'title' => 'Administration Guide',
                'slug' => 'admin-guide',
                'file_path' => 'admin/admin-guide.md',
                'description' => 'System administration guide',
                'category' => 'admin',
                'order' => 1,
                'version' => '1.0',
                'content' => file_get_contents(base_path('docs/admin/admin-guide.md')),
                'is_published' => true,
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
            ],

            // API & Technical
            [
                'title' => 'API Reference',
                'slug' => 'api-reference',
                'file_path' => 'api/api-reference.md',
                'description' => 'Complete API documentation',
                'category' => 'api',
                'order' => 1,
                'version' => '1.0',
                'content' => file_get_contents(base_path('docs/api/api-reference.md')),
                'is_published' => true,
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
            ],
            [
                'title' => 'REST API',
                'slug' => 'rest-api',
                'file_path' => 'api/rest-api.md',
                'description' => 'REST endpoints and integration',
                'category' => 'api',
                'order' => 2,
                'version' => '1.0',
                'content' => file_get_contents(base_path('docs/api/rest-api.md')),
                'is_published' => true,
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
            ],
            [
                'title' => 'WebSocket Implementation',
                'slug' => 'websocket-implementation',
                'file_path' => 'api/websocket-implementation.md',
                'description' => 'Real-time features and WebSocket setup',
                'category' => 'api',
                'order' => 3,
                'version' => '1.0',
                'content' => file_get_contents(base_path('docs/api/websocket-implementation.md')),
                'is_published' => true,
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
            ],

            // Deployment
            [
                'title' => 'Deployment Guide',
                'slug' => 'deployment-guide',
                'file_path' => 'deployment/deployment-guide.md',
                'description' => 'Production deployment instructions',
                'category' => 'deployment',
                'order' => 1,
                'version' => '1.0',
                'content' => file_get_contents(base_path('docs/deployment/deployment-guide.md')),
                'is_published' => true,
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
            ],
            [
                'title' => 'Email Server Setup',
                'slug' => 'email-server-setup',
                'file_path' => 'deployment/email-server-setup.md',
                'description' => 'Email integration and server configuration',
                'category' => 'deployment',
                'order' => 2,
                'version' => '1.0',
                'content' => file_get_contents(base_path('docs/deployment/email-server-setup.md')),
                'is_published' => true,
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
            ],

            // Development
            [
                'title' => 'Advanced Features',
                'slug' => 'advanced-features',
                'file_path' => 'development/advanced-features.md',
                'description' => 'Advanced functionality and customization',
                'category' => 'development',
                'order' => 1,
                'version' => '1.0',
                'content' => file_get_contents(base_path('docs/development/advanced-features.md')),
                'is_published' => true,
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
            ],
            [
                'title' => 'Search Integration',
                'slug' => 'search-integration',
                'file_path' => 'development/search-integration.md',
                'description' => 'Search system implementation and customization',
                'category' => 'development',
                'order' => 2,
                'version' => '1.0',
                'content' => file_get_contents(base_path('docs/development/search-integration.md')),
                'is_published' => true,
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
            ],
            [
                'title' => 'GitHub Webhook Setup',
                'slug' => 'github-webhook-setup',
                'file_path' => 'development/github-webhook-setup.md',
                'description' => 'GitHub integration and webhook configuration',
                'category' => 'development',
                'order' => 3,
                'version' => '1.0',
                'content' => file_get_contents(base_path('docs/development/github-webhook-setup.md')),
                'is_published' => true,
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
            ],
            [
                'title' => 'Markdown Guide',
                'slug' => 'markdown-guide',
                'file_path' => 'development/markdown-guide.md',
                'description' => 'Complete guide to GitHub-flavored markdown features supported in TIKM',
                'category' => 'development',
                'order' => 4,
                'version' => '1.0',
                'content' => file_get_contents(base_path('docs/development/markdown-guide.md')),
                'is_published' => true,
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
            ],
        ];

        foreach ($docs as $docData) {
            Documentation::updateOrCreate(
                ['slug' => $docData['slug']],
                $docData
            );
        }
    }
}
