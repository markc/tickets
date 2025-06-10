# Markdown Test Document

This document tests all GitHub-flavored markdown features to verify accurate rendering.

## Text Formatting

**Bold text** using double asterisks

*Italic text* using single asterisks  

***Bold and italic*** using triple asterisks

~~Strikethrough text~~ using double tildes

`Inline code` using backticks

Regular text with [links](https://example.com) and automatic URLs like https://github.com

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

class Documentation extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'category',
        'is_published'
    ];

    public function getRenderedContentAttribute(): string
    {
        $converter = new MarkdownConverter($this->environment);
        return $converter->convert($this->content)->getContent();
    }
}
```

#### Bash Commands

```bash
# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Development server
php artisan serve --host=0.0.0.0 --port=8000
```

#### JavaScript

```javascript
// Vite configuration
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
```

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

## Tables

| Feature | Status | Description |
|---------|--------|-------------|
| Tickets | ✅ Complete | Full ticket management |
| FAQs | ✅ Complete | Knowledge base system |
| Users | ✅ Complete | Role-based access control |
| Reports | 🚧 In Progress | Analytics dashboard |
| API | ✅ Complete | REST API endpoints |

## Blockquotes

> **Important**: Always backup your database before running migrations in production.

> **Tip**: Use `composer dev` to start all development services at once.

> **Warning**: The default credentials should be changed immediately in production environments.

## Horizontal Rules

Content above the line.

---

Content below the line.

## Final Test

This tests if `inline code` works properly and if code blocks have proper syntax highlighting.

```json
{
    "name": "markdown-test",
    "version": "1.0.0",
    "dependencies": {
        "laravel-echo": "^1.16.0",
        "pusher-js": "^8.4.0-rc2"
    }
}
```

End of test document.