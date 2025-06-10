<x-documentation-layout>
    <div class="mx-auto max-w-4xl px-6 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                Search Documentation
            </h1>
            <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">
                Find answers in our comprehensive documentation
            </p>
        </div>

        <!-- Search Form -->
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 mb-8">
            <form action="{{ route('documentation.search') }}" method="GET">
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <input type="text" 
                               name="q" 
                               value="{{ $query }}"
                               placeholder="Search documentation..." 
                               class="block w-full rounded-lg border-0 py-3 px-4 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/10 dark:placeholder:text-gray-500 sm:text-sm sm:leading-6"
                               autofocus>
                    </div>
                    <button type="submit" 
                            class="fi-btn fi-color-primary fi-btn-color-primary inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-75 hover:bg-primary-500 focus:ring-2 focus:ring-primary-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"></path>
                        </svg>
                        Search
                    </button>
                </div>
            </form>
        </div>

        @if($query)
            <!-- Search Results -->
            <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-950 dark:text-white">
                        Search Results
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $results->count() }} {{ Str::plural('result', $results->count()) }} for "{{ $query }}"
                    </p>
                </div>

                @if($results->count() > 0)
                    <div class="space-y-6">
                        @foreach($results as $result)
                        <div class="border-b border-gray-200 dark:border-gray-800 pb-6 last:border-b-0 last:pb-0">
                            <div class="flex items-start justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="mb-2 flex items-center gap-2">
                                        <span class="fi-badge fi-color-{{ match($result->category) {
                                            'overview' => 'gray',
                                            'user' => 'primary',
                                            'admin' => 'warning',
                                            'api' => 'success',
                                            'deployment' => 'danger',
                                            'development' => 'info',
                                            default => 'gray'
                                        } }} fi-size-sm inline-flex items-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset capitalize">
                                            {{ $result->category }}
                                        </span>
                                        
                                        @if($result->version)
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            v{{ $result->version }}
                                        </span>
                                        @endif
                                    </div>
                                    
                                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white mb-2">
                                        <a href="{{ route('documentation.show', $result) }}" 
                                           class="hover:text-primary-600 dark:hover:text-primary-400">
                                            {{ $result->title }}
                                        </a>
                                    </h3>
                                    
                                    @if($result->description)
                                    <p class="text-gray-600 dark:text-gray-400 mb-2">
                                        {{ $result->description }}
                                    </p>
                                    @endif
                                    
                                    <p class="text-sm text-gray-500 dark:text-gray-500">
                                        {{ $result->excerpt }}
                                    </p>
                                    
                                    <div class="mt-3 flex items-center gap-x-4 text-sm text-gray-500 dark:text-gray-400">
                                        <span>Updated {{ $result->updated_at->format('M j, Y') }}</span>
                                        @if($result->updatedBy)
                                        <span>by {{ $result->updatedBy->name }}</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <a href="{{ route('documentation.show', $result) }}" 
                                   class="group ml-4 flex items-center gap-x-2 text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
                                    View
                                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <h3 class="mt-4 text-sm font-medium text-gray-950 dark:text-white">No results found</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Try searching with different keywords or browse the documentation categories.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('documentation.index') }}" 
                               class="fi-btn fi-color-primary fi-btn-color-primary inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-75 hover:bg-primary-500 focus:ring-2 focus:ring-primary-600">
                                Browse Documentation
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <!-- Search Tips -->
            <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h2 class="text-xl font-semibold text-gray-950 dark:text-white mb-6">
                    Search Tips
                </h2>
                
                <div class="space-y-6">
                    <div>
                        <h3 class="font-medium text-gray-950 dark:text-white">Use specific keywords</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Search for specific features like "email integration", "ticket creation", or "user management"
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="font-medium text-gray-950 dark:text-white">Try different terms</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            If you don't find what you're looking for, try synonyms or related terms
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="font-medium text-gray-950 dark:text-white">Browse categories</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Sometimes browsing by category can help you discover relevant documentation
                        </p>
                    </div>
                </div>
                
                <div class="mt-8 border-t border-gray-200 pt-6 dark:border-gray-800">
                    <h3 class="font-medium text-gray-950 dark:text-white mb-3">Popular searches</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['quick start', 'email setup', 'user guide', 'API reference', 'deployment'] as $term)
                        <a href="{{ route('documentation.search', ['q' => $term]) }}" 
                           class="inline-flex items-center rounded-md bg-gray-50 px-3 py-1 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-500/10 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-500/20 dark:hover:bg-gray-700">
                            {{ $term }}
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-documentation-layout>