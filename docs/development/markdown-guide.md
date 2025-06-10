---
title: "Markdown Guide for TIKM Documentation"
description: "Complete guide to GitHub-flavored markdown features supported in TIKM"
category: "development"
order: 4
version: "1.0"
last_updated: "2025-06-10"
---

# Markdown Guide for TIKM Documentation

TIKM uses an enhanced GitHub-flavored markdown system powered by **CommonMark** with syntax highlighting and extended features. This guide covers all supported markdown features and provides examples.

## Markdown in TIKM

### Features Supported

TIKM's documentation system supports:

- ✅ **GitHub-Flavored Markdown** - Full GFM specification
- ✅ **Syntax Highlighting** - Code blocks with language-specific coloring
- ✅ **Tables** - Full table support with styling
- ✅ **Task Lists** - Interactive checkbox lists
- ✅ **Strikethrough** - Text formatting
- ✅ **Autolinks** - Automatic URL detection
- ✅ **Front Matter** - YAML metadata support
- ✅ **Dark/Light Mode** - Automatic theme switching

### Rendering Engine

```php
<?php
// TIKM uses League\CommonMark with extensions
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use Spatie\CommonMarkHighlighter\HighlightCodeExtension;

$environment = new Environment();
$environment->addExtension(new GithubFlavoredMarkdownExtension());
$environment->addExtension(new HighlightCodeExtension());
```

## Headers

# H1 Header - Main Title
## H2 Header - Major Section  
### H3 Header - Subsection
#### H4 Header - Sub-subsection
##### H5 Header - Minor Section
###### H6 Header - Smallest

## Text Formatting

**Bold text** using double asterisks

*Italic text* using single asterisks

***Bold and italic*** using triple asterisks

~~Strikethrough text~~ using double tildes

`Inline code` using backticks

Regular text with [links](https://example.com) and automatic URLs like https://github.com

## Lists

### Unordered Lists

- First item
- Second item
  - Nested item
  - Another nested item
    - Deeply nested
- Back to top level

### Ordered Lists

1. First numbered item
2. Second numbered item
   1. Nested numbered item
   2. Another nested item
3. Third item

### Task Lists

- [x] Completed task
- [x] Another completed task
- [ ] Incomplete task
- [ ] Another incomplete task
  - [x] Nested completed task
  - [ ] Nested incomplete task

## Code Examples

### Inline Code

Use `composer install` to install dependencies.

The `php artisan serve` command starts the development server.

### Code Blocks

#### PHP Code

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Documentation extends Model
{
    use Searchable;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'category',
        'is_published'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'last_updated' => 'timestamp',
    ];

    public function getRenderedContentAttribute(): string
    {
        $converter = new MarkdownConverter($this->environment);
        return $converter->convert($this->content)->getContent();
    }
}
```

#### Bash/Shell Commands

```bash
# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database operations
php artisan migrate
php artisan db:seed

# Development server
php artisan serve --host=0.0.0.0 --port=8000

# Queue processing
php artisan queue:listen --timeout=60

# Cache operations
php artisan cache:clear
php artisan config:cache
php artisan route:cache
```

#### JavaScript/Node.js

```javascript
// Vite configuration for TIKM
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/ticket-realtime.js'
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost',
        },
    },
});
```

#### SQL Examples

```sql
-- Create tickets table
CREATE TABLE tickets (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    uuid VARCHAR(36) NOT NULL UNIQUE,
    subject VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    office_id BIGINT UNSIGNED NOT NULL,
    creator_id BIGINT UNSIGNED NOT NULL,
    assigned_to_id BIGINT UNSIGNED NULL,
    ticket_status_id BIGINT UNSIGNED NOT NULL,
    ticket_priority_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY tickets_office_id_foreign (office_id),
    KEY tickets_creator_id_foreign (creator_id),
    KEY tickets_assigned_to_id_foreign (assigned_to_id)
);

-- Query for ticket statistics
SELECT 
    ts.name as status,
    COUNT(*) as count,
    AVG(TIMESTAMPDIFF(HOUR, t.created_at, COALESCE(t.updated_at, NOW()))) as avg_hours
FROM tickets t
JOIN ticket_statuses ts ON t.ticket_status_id = ts.id
WHERE t.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY ts.id, ts.name
ORDER BY count DESC;
```

#### JSON Configuration

```json
{
    "name": "tikm",
    "version": "1.0.0",
    "description": "TIKM Customer Support System",
    "scripts": {
        "dev": "vite",
        "build": "vite build",
        "postinstall": "php artisan filament:upgrade"
    },
    "devDependencies": {
        "@tailwindcss/forms": "^0.5.7",
        "@tailwindcss/typography": "^0.5.13",
        "autoprefixer": "^10.4.19",
        "laravel-vite-plugin": "^1.0.0",
        "postcss": "^8.4.38",
        "tailwindcss": "^3.4.0",
        "vite": "^6.0.0"
    },
    "dependencies": {
        "laravel-echo": "^1.16.0",
        "pusher-js": "^8.4.0-rc2"
    }
}
```

#### YAML Front Matter

```yaml
---
title: "Example Documentation Page"
description: "This shows how front matter works"
category: "user"
order: 1
version: "1.0"
last_updated: "2025-06-10"
author: "TIKM Team"
tags:
  - documentation
  - markdown
  - examples
meta:
  canonical: "/docs/example"
  robots: "index,follow"
---
```

## Tables

### Basic Table

| Feature | Status | Description |
|---------|--------|-------------|
| Tickets | ✅ Complete | Full ticket management |
| FAQs | ✅ Complete | Knowledge base system |
| Users | ✅ Complete | Role-based access control |
| Reports | 🚧 In Progress | Analytics dashboard |
| API | ✅ Complete | REST API endpoints |

### Complex Table with Alignment

| Component | Technology | Purpose | Performance |
|:----------|:----------:|--------:|------------:|
| **Backend** | Laravel 12 | Application framework | Excellent |
| **Frontend** | Vite + Tailwind | Build tools & styling | Fast |
| **Database** | SQLite/MySQL | Data persistence | Good |
| **Search** | Laravel Scout | Full-text search | Very Good |
| **Cache** | Redis/Database | Performance optimization | Excellent |

### Table with Code

| Command | Description | Example |
|---------|-------------|---------|
| `migrate` | Run database migrations | `php artisan migrate` |
| `seed` | Populate database with test data | `php artisan db:seed` |
| `serve` | Start development server | `php artisan serve --port=8080` |
| `queue:work` | Process background jobs | `php artisan queue:work --daemon` |

## Blockquotes

> **Important**: Always backup your database before running migrations in production.

> **Tip**: Use `composer dev` to start all development services at once.

> **Warning**: The default credentials should be changed immediately in production environments.

### Nested Blockquotes

> This is a top-level quote.
> 
> > This is a nested quote within the first quote.
> > 
> > > This is even more deeply nested.
> 
> Back to the top level.

## Links and References

### External Links

Visit the [Laravel Documentation](https://laravel.com/docs) for framework details.

Check out [Filament](https://filamentphp.com) for admin panel features.

### Internal Documentation Links

- [Quick Start Guide](/admin/documentation/quick-start)
- [API Reference](/admin/documentation/api-reference)
- [Deployment Guide](/admin/documentation/deployment-guide)

### Reference-style Links

TIKM is built with [Laravel][laravel] and uses [Filament][filament] for the admin interface.

[laravel]: https://laravel.com "Laravel PHP Framework"
[filament]: https://filamentphp.com "Filament Admin Panel"

## Images and Media

### Basic Image

![TIKM Logo](https://via.placeholder.com/200x100/4F46E5/FFFFFF?text=TIKM)

### Image with Link

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-blue.svg)](https://php.net)
[![Filament](https://img.shields.io/badge/Filament-3.3-orange.svg)](https://filamentphp.com)

## Horizontal Rules

Content above the line.

---

Content below the line.

***

Another horizontal rule style.

## Escape Characters

You can escape markdown characters with backslashes:

\*This text is not italic\*

\`This is not code\`

\# This is not a header

## HTML Support

<div style="background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 8px; padding: 16px; margin: 16px 0;">
    <strong>📘 Note:</strong> TIKM supports limited HTML within markdown for enhanced formatting.
</div>

<details>
<summary>Click to expand advanced configuration</summary>

```php
// Advanced TIKM configuration
return [
    'markdown' => [
        'extensions' => [
            'table' => true,
            'strikethrough' => true,
            'autolink' => true,
            'task_lists' => true,
            'syntax_highlighting' => true,
        ],
        'security' => [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ],
    ],
];
```

</details>

## Best Practices for TIKM Documentation

### 1. Use Front Matter

Always include YAML front matter for proper categorization:

```yaml
---
title: "Page Title"
description: "Brief description"
category: "user|admin|api|deployment|development|overview"
order: 1
version: "1.0"
last_updated: "2025-06-10"
---
```

### 2. Structure Your Content

- Use descriptive headers (H2, H3) for sections
- Include a table of contents for long documents
- Use consistent formatting throughout

### 3. Code Examples

- Always specify the language for syntax highlighting
- Include complete, working examples
- Add comments to explain complex code

### 4. Cross-References

- Link to related documentation pages
- Use consistent link formats
- Verify all internal links work

### 5. Accessibility

- Use descriptive link text
- Include alt text for images
- Structure content with proper heading hierarchy

## Markdown Cheat Sheet

| Element | Syntax |
|---------|--------|
| **Header 1** | `# H1` |
| **Header 2** | `## H2` |
| **Bold** | `**text**` |
| **Italic** | `*text*` |
| **Code** | `` `code` `` |
| **Link** | `[text](url)` |
| **Image** | `![alt](url)` |
| **List** | `- item` |
| **Numbered** | `1. item` |
| **Quote** | `> quote` |
| **Table** | `| col1 | col2 |` |
| **Task** | `- [ ] task` |
| **Strike** | `~~text~~` |

---

*This guide covers all markdown features supported in TIKM's documentation system. For technical details about the implementation, see the [Development Setup](/admin/documentation/development-setup) guide.*