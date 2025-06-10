<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Search Documentation') }}
            </h2>
            <a href="{{ route('documentation.index') }}" 
               class="inline-flex items-center text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                <x-heroicon-o-arrow-left class="h-4 w-4 mr-2"/>
                Back to Documentation
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Search Form -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <form action="{{ route('documentation.search') }}" method="GET">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <input type="text" 
                                       name="q" 
                                       value="{{ $query }}"
                                       placeholder="Search documentation..." 
                                       class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600"
                                       autofocus>
                            </div>
                            <button type="submit" 
                                    class="ml-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-heroicon-o-magnifying-glass class="h-4 w-4 mr-2"/>
                                Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if($query)
                <!-- Search Results -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                Search Results
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $results->count() }} {{ Str::plural('result', $results->count()) }} for "{{ $query }}"
                            </p>
                        </div>

                        @if($results->count() > 0)
                            <div class="space-y-6">
                                @foreach($results as $result)
                                <div class="border-b border-gray-200 dark:border-gray-700 pb-6 last:border-b-0 last:pb-0">
                                    <div class="flex items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center mb-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize mr-3 {{ match($result->category) {
                                                    'overview' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                                    'user' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                                    'admin' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300',
                                                    'api' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                                    'deployment' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                                    'development' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
                                                    default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                                } }}">
                                                    {{ $result->category }}
                                                </span>
                                                
                                                @if($result->version)
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    v{{ $result->version }}
                                                </span>
                                                @endif
                                            </div>
                                            
                                            <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                                <a href="{{ route('documentation.show', $result) }}" 
                                                   class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                                    {{ $result->title }}
                                                </a>
                                            </h4>
                                            
                                            @if($result->description)
                                            <p class="text-gray-600 dark:text-gray-400 mb-2">
                                                {{ $result->description }}
                                            </p>
                                            @endif
                                            
                                            <p class="text-sm text-gray-500 dark:text-gray-500">
                                                {{ $result->excerpt }}
                                            </p>
                                            
                                            <div class="flex items-center mt-3 text-sm text-gray-500 dark:text-gray-400">
                                                <span>Updated {{ $result->updated_at->format('M j, Y') }}</span>
                                                @if($result->updatedBy)
                                                <span class="mx-2">•</span>
                                                <span>by {{ $result->updatedBy->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <a href="{{ route('documentation.show', $result) }}" 
                                           class="ml-4 inline-flex items-center text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300">
                                            View
                                            <x-heroicon-o-arrow-right class="h-4 w-4 ml-1"/>
                                        </a>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <x-heroicon-o-document-magnifying-glass class="mx-auto h-12 w-12 text-gray-400"/>
                                <h3 class="mt-4 text-sm font-medium text-gray-900 dark:text-white">No results found</h3>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Try searching with different keywords or browse the documentation categories.
                                </p>
                                <div class="mt-6">
                                    <a href="{{ route('documentation.index') }}" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 dark:bg-indigo-900 dark:text-indigo-200 dark:hover:bg-indigo-800">
                                        Browse Documentation
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <!-- Search Tips -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            Search Tips
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Use specific keywords</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Search for specific features like "email integration", "ticket creation", or "user management"
                                </p>
                            </div>
                            
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Try different terms</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    If you don't find what you're looking for, try synonyms or related terms
                                </p>
                            </div>
                            
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Browse categories</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Sometimes browsing by category can help you discover relevant documentation
                                </p>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h4 class="font-medium text-gray-900 dark:text-white mb-3">Popular searches</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach(['quick start', 'email setup', 'user guide', 'API reference', 'deployment'] as $term)
                                <a href="{{ route('documentation.search', ['q' => $term]) }}" 
                                   class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">
                                    {{ $term }}
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>