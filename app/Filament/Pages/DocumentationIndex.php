<?php

namespace App\Filament\Pages;

use App\Models\Documentation;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class DocumentationIndex extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static string $view = 'filament.pages.documentation-index';

    protected static ?string $slug = 'documentation/{slug?}';

    protected static ?string $title = 'Documentation';

    protected static ?string $navigationLabel = 'Documentation';

    protected static ?int $navigationSort = 12;

    public $docSlug = 'index';

    public function mount($slug = 'index')
    {
        $this->docSlug = $slug;
    }

    public function getTitle(): string|Htmlable
    {
        $doc = $this->getDocumentation();

        return $doc ? $doc->title : 'Documentation';
    }

    public function getDocumentation()
    {
        return Documentation::where('slug', $this->docSlug)->first();
    }

    public function getIndexDocumentation()
    {
        return Documentation::where('slug', 'index')->first();
    }
}
