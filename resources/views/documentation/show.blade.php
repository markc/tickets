<x-documentation-layout>
    <div class="flex h-full">
        <!-- Main Content Area -->
        <div class="flex-1 overflow-hidden">
            <div class="flex h-full">
                <!-- Content -->
                <main class="flex-1 overflow-y-auto">
                    <div class="mx-auto max-w-4xl px-6 py-8">
                        <!-- Article Header -->
                        <header class="mb-8">
                            <div class="mb-4 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="fi-badge fi-color-{{ match($documentation->category) {
                                        'overview' => 'gray',
                                        'user' => 'primary',
                                        'admin' => 'warning',
                                        'api' => 'success',
                                        'deployment' => 'danger',
                                        'development' => 'info',
                                        default => 'gray'
                                    } }} fi-size-sm inline-flex items-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset capitalize">
                                        {{ $documentation->category }}
                                    </span>
                                    
                                    @if($documentation->version)
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        v{{ $documentation->version }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                            
                            <h1 class="text-3xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-4xl">
                                {{ $documentation->title }}
                            </h1>
                            
                            @if($documentation->description)
                            <p class="mt-4 text-xl text-gray-600 dark:text-gray-400">
                                {{ $documentation->description }}
                            </p>
                            @endif
                            
                            <div class="mt-6 flex items-center gap-x-4 text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex items-center gap-x-2">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    @if($documentation->last_updated)
                                    <span>Last updated {{ $documentation->last_updated->format('M j, Y') }}</span>
                                    @else
                                    <span>Updated {{ $documentation->updated_at->format('M j, Y') }}</span>
                                    @endif
                                </div>
                                
                                @if($documentation->updatedBy)
                                <div class="flex items-center gap-x-2">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span>by {{ $documentation->updatedBy->name }}</span>
                                </div>
                                @endif
                            </div>
                        </header>

                        <!-- Article Content -->
                        <div class="prose prose-lg prose-gray max-w-none dark:prose-invert">
                            {!! $documentation->rendered_content !!}
                        </div>

                        <!-- Navigation Footer -->
                        <nav class="mt-12 border-t border-gray-200 pt-8 dark:border-gray-800">
                            <div class="flex justify-between">
                                @if($previousDoc)
                                <a href="{{ route('documentation.show', $previousDoc) }}" 
                                   class="group flex items-center gap-x-2 text-sm font-medium text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                    <svg class="h-4 w-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                    <span>{{ $previousDoc->title }}</span>
                                </a>
                                @else
                                <div></div>
                                @endif

                                @if($nextDoc)
                                <a href="{{ route('documentation.show', $nextDoc) }}" 
                                   class="group flex items-center gap-x-2 text-sm font-medium text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                    <span>{{ $nextDoc->title }}</span>
                                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                                @endif
                            </div>
                        </nav>
                    </div>
                </main>

                <!-- Table of Contents Sidebar -->
                @if(count($tableOfContents) > 0)
                <aside class="hidden w-64 flex-shrink-0 xl:block">
                    <div class="sticky top-0 h-screen overflow-y-auto py-8">
                        <div class="px-6">
                            <h3 class="text-sm font-semibold text-gray-950 dark:text-white">On this page</h3>
                            <nav class="mt-4">
                                <ul class="space-y-2 text-sm">
                                    @foreach($tableOfContents as $item)
                                    <li style="padding-left: {{ ($item['level'] - 1) * 16 }}px">
                                        <a href="#{{ $item['anchor'] }}" 
                                           class="block text-gray-600 transition-colors hover:text-gray-950 dark:text-gray-400 dark:hover:text-white">
                                            {{ $item['title'] }}
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                            </nav>
                        </div>
                    </div>
                </aside>
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