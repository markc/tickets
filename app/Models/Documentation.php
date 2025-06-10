<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use Spatie\CommonMarkHighlighter\FencedCodeRenderer;
use Spatie\CommonMarkHighlighter\IndentedCodeRenderer;

class Documentation extends Model
{
    use Searchable;

    protected $fillable = [
        'title',
        'slug',
        'file_path',
        'description',
        'category',
        'order',
        'version',
        'content',
        'meta',
        'is_published',
        'last_updated',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_published' => 'boolean',
        'last_updated' => 'timestamp',
    ];

    protected static function booted(): void
    {
        static::creating(function (Documentation $documentation) {
            if (empty($documentation->slug)) {
                $documentation->slug = Str::slug($documentation->title);
            }
            $documentation->created_by = auth()->id();
            $documentation->updated_by = auth()->id();
        });

        static::updating(function (Documentation $documentation) {
            $documentation->updated_by = auth()->id();
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getRenderedContentAttribute(): string
    {
        // Create environment with GitHub-flavored markdown extensions
        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => PHP_INT_MAX,
        ]);

        // Add core CommonMark functionality
        $environment->addExtension(new CommonMarkCoreExtension);

        // Add GitHub-flavored markdown extensions (includes tables, strikethrough, autolinks, task lists)
        $environment->addExtension(new GithubFlavoredMarkdownExtension);

        // Add syntax highlighting renderers
        $environment->addRenderer(FencedCode::class, new FencedCodeRenderer);
        $environment->addRenderer(IndentedCode::class, new IndentedCodeRenderer);

        $converter = new MarkdownConverter($environment);

        return $converter->convert($this->getContentWithoutFrontMatter())->getContent();
    }

    public function getExcerptAttribute(): string
    {
        return Str::limit(strip_tags($this->rendered_content), 150);
    }

    public function getMetaAttribute($value)
    {
        $meta = json_decode($value, true) ?? [];

        // Parse front matter from content if meta is empty
        if (empty($meta) && $this->content) {
            $meta = $this->parseFrontMatter($this->content);
        }

        return $meta;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('title');
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'content' => strip_tags($this->rendered_content),
            'category' => $this->category,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->is_published;
    }

    private function parseFrontMatter(string $content): array
    {
        if (! str_starts_with($content, '---')) {
            return [];
        }

        $parts = explode('---', $content, 3);
        if (count($parts) < 3) {
            return [];
        }

        $frontMatter = trim($parts[1]);
        $meta = [];

        foreach (explode("\n", $frontMatter) as $line) {
            if (str_contains($line, ':')) {
                [$key, $value] = explode(':', $line, 2);
                $meta[trim($key)] = trim($value, ' "\'');
            }
        }

        return $meta;
    }

    public function getContentWithoutFrontMatter(): string
    {
        if (! str_starts_with($this->content, '---')) {
            return $this->content;
        }

        $parts = explode('---', $this->content, 3);

        return count($parts) >= 3 ? trim($parts[2]) : $this->content;
    }

    public static function getCategoriesWithCounts(): array
    {
        return static::published()
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->orderBy('category')
            ->pluck('count', 'category')
            ->toArray();
    }
}
