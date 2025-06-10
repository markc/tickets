<x-app-layout>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center">
                        <a href="{{ route('documentation.index') }}" 
                           class="flex items-center text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                            <x-heroicon-o-arrow-left class="h-4 w-4 mr-2"/>
                            Documentation
                        </a>
                        <span class="mx-2 text-gray-400">/</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ $documentation->category }}</span>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Search -->
                        <form action="{{ route('documentation.search') }}" method="GET" class="flex items-center">
                            <input type="text" name="q" placeholder="Search docs..." 
                                   class="w-64 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 text-sm">
                            <button type="submit" class="ml-2 p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <x-heroicon-o-magnifying-glass class="h-4 w-4"/>
                            </button>
                        </form>
                        
                        @auth
                            @if(auth()->user()->role === 'admin')
                            <a href="{{ route('filament.admin.resources.documentations.edit', $documentation) }}" 
                               class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <x-heroicon-o-pencil class="h-3 w-3 mr-1"/>
                                Edit
                            </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex gap-8">
                <!-- Left Sidebar Navigation -->
                <div class="hidden lg:block w-64 flex-shrink-0">
                    <div class="sticky top-6">
                        <nav class="space-y-1">
                            @foreach($navigation as $categorySlug => $categoryData)
                            <div class="mb-6">
                                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                                    {{ $categoryData['name'] }}
                                </h3>
                                <ul class="space-y-1">
                                    @foreach($categoryData['docs'] as $doc)
                                    <li>
                                        <a href="{{ route('documentation.show', $doc) }}" 
                                           class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ $doc->id === $documentation->id ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                                            {{ $doc->title }}
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endforeach
                        </nav>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="flex-1 max-w-4xl">
                    <article class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                        <!-- Article Header -->
                        <div class="px-6 py-6 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize {{ match($documentation->category) {
                                    'overview' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                    'user' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                    'admin' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300',
                                    'api' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                    'deployment' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                    'development' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
                                    default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                } }}">
                                    {{ $documentation->category }}
                                </span>
                                
                                @if($documentation->version)
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    v{{ $documentation->version }}
                                </span>
                                @endif
                            </div>
                            
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                                {{ $documentation->title }}
                            </h1>
                            
                            @if($documentation->description)
                            <p class="text-lg text-gray-600 dark:text-gray-400 mb-4">
                                {{ $documentation->description }}
                            </p>
                            @endif
                            
                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                @if($documentation->last_updated)
                                <span>Last updated {{ $documentation->last_updated->format('M j, Y') }}</span>
                                @else
                                <span>Updated {{ $documentation->updated_at->format('M j, Y') }}</span>
                                @endif
                                
                                @if($documentation->updatedBy)
                                <span class="mx-2">•</span>
                                <span>by {{ $documentation->updatedBy->name }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Article Content -->
                        <div class="px-6 py-6">
                            <div class="prose prose-lg dark:prose-invert max-w-none">
                                {!! $documentation->rendered_content !!}
                            </div>
                        </div>

                        <!-- Navigation Footer -->
                        <div class="px-6 py-6 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between">
                                @if($previousDoc)
                                <a href="{{ route('documentation.show', $previousDoc) }}" 
                                   class="flex items-center text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300">
                                    <x-heroicon-o-arrow-left class="h-4 w-4 mr-2"/>
                                    {{ $previousDoc->title }}
                                </a>
                                @else
                                <div></div>
                                @endif

                                @if($nextDoc)
                                <a href="{{ route('documentation.show', $nextDoc) }}" 
                                   class="flex items-center text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300">
                                    {{ $nextDoc->title }}
                                    <x-heroicon-o-arrow-right class="h-4 w-4 ml-2"/>
                                </a>
                                @endif
                            </div>
                        </div>
                    </article>
                </div>

                <!-- Right Sidebar - Table of Contents -->
                @if(count($tableOfContents) > 0)
                <div class="hidden xl:block w-64 flex-shrink-0">
                    <div class="sticky top-6">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">On this page</h3>
                            <nav class="space-y-1">
                                @foreach($tableOfContents as $item)
                                <a href="#{{ $item['anchor'] }}" 
                                   class="block text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors {{ $item['level'] > 1 ? 'ml-' . (($item['level'] - 1) * 3) : '' }}"
                                   style="padding-left: {{ ($item['level'] - 1) * 12 }}px">
                                    {{ $item['title'] }}
                                </a>
                                @endforeach
                            </nav>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Add anchor links to headings
        document.addEventListener('DOMContentLoaded', function() {
            const headings = document.querySelectorAll('h1, h2, h3, h4, h5, h6');
            headings.forEach(heading => {
                const text = heading.textContent;
                const anchor = text.toLowerCase().replace(/[^\w\s]/g, '').replace(/\s+/g, '-');
                heading.id = anchor;
                
                // Add anchor link
                const link = document.createElement('a');
                link.href = '#' + anchor;
                link.className = 'anchor-link opacity-0 hover:opacity-100 ml-2 text-gray-400 hover:text-gray-600';
                link.innerHTML = '#';
                heading.appendChild(link);
            });

            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });
    </script>
    @endpush

    @push('styles')
    <style>
        .anchor-link {
            transition: opacity 0.2s ease;
        }
        
        h1:hover .anchor-link,
        h2:hover .anchor-link,
        h3:hover .anchor-link,
        h4:hover .anchor-link,
        h5:hover .anchor-link,
        h6:hover .anchor-link {
            opacity: 1;
        }
        
        .prose h1, .prose h2, .prose h3 {
            scroll-margin-top: 2rem;
        }
    </style>
    @endpush
</x-app-layout>