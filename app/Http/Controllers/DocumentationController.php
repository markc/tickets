<?php

namespace App\Http\Controllers;

use App\Models\Documentation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentationController extends Controller
{
    public function index(): View
    {
        $categories = Documentation::published()
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $documentsByCategory = [];
        foreach ($categories as $category) {
            $documentsByCategory[$category] = Documentation::published()
                ->byCategory($category)
                ->ordered()
                ->get();
        }

        $featuredDocs = Documentation::published()
            ->whereIn('slug', ['quick-start', 'user-guide', 'admin-guide'])
            ->get()
            ->keyBy('slug');

        return view('documentation.index', compact('documentsByCategory', 'featuredDocs'));
    }

    public function show(Documentation $documentation): View
    {
        // Only show published docs to non-admin users
        if (! $documentation->is_published && ! auth()->user()?->role === 'admin') {
            abort(404);
        }

        // Get navigation structure
        $navigation = $this->getNavigationStructure();

        // Get current document's position in navigation for prev/next
        $currentCategoryDocs = Documentation::published()
            ->byCategory($documentation->category)
            ->ordered()
            ->get();

        $currentIndex = $currentCategoryDocs->search(fn ($doc) => $doc->id === $documentation->id);
        $previousDoc = $currentIndex > 0 ? $currentCategoryDocs[$currentIndex - 1] : null;
        $nextDoc = $currentIndex < $currentCategoryDocs->count() - 1 ? $currentCategoryDocs[$currentIndex + 1] : null;

        // Generate table of contents from headings
        $tableOfContents = $this->generateTableOfContents($documentation->content);

        return view('documentation.show', compact(
            'documentation',
            'navigation',
            'previousDoc',
            'nextDoc',
            'tableOfContents'
        ));
    }

    public function search(Request $request): View
    {
        $query = $request->get('q');
        $results = collect();

        if ($query) {
            $results = Documentation::search($query)
                ->where('is_published', true)
                ->get();
        }

        return view('documentation.search', compact('query', 'results'));
    }

    private function getNavigationStructure(): array
    {
        $categories = [
            'overview' => 'Overview',
            'user' => 'User Guide',
            'admin' => 'Administration',
            'api' => 'API & Technical',
            'deployment' => 'Deployment',
            'development' => 'Development',
        ];

        $navigation = [];
        foreach ($categories as $slug => $name) {
            $docs = Documentation::published()
                ->byCategory($slug)
                ->ordered()
                ->get(['id', 'title', 'slug', 'description']);

            if ($docs->isNotEmpty()) {
                $navigation[$slug] = [
                    'name' => $name,
                    'docs' => $docs,
                ];
            }
        }

        return $navigation;
    }

    private function generateTableOfContents(string $content): array
    {
        $toc = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            if (preg_match('/^(#{1,3})\s+(.+)$/', $line, $matches)) {
                $level = strlen($matches[1]);
                $title = trim($matches[2]);
                $anchor = \Illuminate\Support\Str::slug($title);

                $toc[] = [
                    'level' => $level,
                    'title' => $title,
                    'anchor' => $anchor,
                ];
            }
        }

        return $toc;
    }
}
