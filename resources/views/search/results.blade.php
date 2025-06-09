<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Search Results') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Saved Searches -->
                    @if($savedSearches->count() > 0)
                        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900 rounded-lg">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100">Saved Searches</h3>
                                <button type="button" onclick="toggleSavedSearches()" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">
                                    <span id="saved-searches-toggle">Show</span>
                                </button>
                            </div>
                            <div id="saved-searches-list" class="hidden">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($savedSearches as $search)
                                        <div class="bg-white dark:bg-gray-800 p-3 rounded border">
                                            <div class="flex justify-between items-start mb-2">
                                                <a href="{{ $search->getSearchUrl() }}" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                                    {{ $search->name }}
                                                </a>
                                                @if($search->user_id === auth()->id() || auth()->user()->isAdmin())
                                                    <button onclick="deleteSavedSearch({{ $search->id }})" class="text-red-500 hover:text-red-700 text-xs">×</button>
                                                @endif
                                            </div>
                                            @if($search->description)
                                                <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">{{ $search->description }}</p>
                                            @endif
                                            <div class="text-xs text-gray-500 dark:text-gray-500">
                                                <div>{{ $search->user->name }} • Used {{ $search->usage_count }} times</div>
                                                @if($search->is_public)
                                                    <span class="inline-block bg-green-100 text-green-800 px-1 rounded">Public</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Search Form with Advanced Filters -->
                    <form method="GET" action="{{ route('search') }}" id="search-form">
                        <!-- Basic Search Row -->
                        <div class="flex gap-4 mb-4">
                            <input type="text" name="q" value="{{ $query }}" 
                                class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600"
                                placeholder="Search tickets and FAQs..." required>
                            <select name="type" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="all" {{ $type === 'all' ? 'selected' : '' }}>All</option>
                                <option value="tickets" {{ $type === 'tickets' ? 'selected' : '' }}>Tickets Only</option>
                                <option value="faqs" {{ $type === 'faqs' ? 'selected' : '' }}>FAQs Only</option>
                            </select>
                            <button type="button" onclick="toggleAdvancedFilters()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                <span id="filters-toggle">Advanced</span>
                            </button>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Search
                            </button>
                        </div>

                        <!-- Advanced Filters (Initially Hidden) -->
                        <div id="advanced-filters" class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg mb-4 {{ empty(array_filter($filters)) ? 'hidden' : '' }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <!-- Status Filter -->
                                <div>
                                    <label class="block text-sm font-medium mb-1">Status</label>
                                    <select name="status[]" multiple class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" size="3">
                                        @foreach($filterOptions['statuses'] as $status)
                                            <option value="{{ $status->name }}" {{ in_array($status->name, $filters['status'] ?? []) ? 'selected' : '' }}>
                                                {{ $status->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Priority Filter -->
                                <div>
                                    <label class="block text-sm font-medium mb-1">Priority</label>
                                    <select name="priority[]" multiple class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" size="3">
                                        @foreach($filterOptions['priorities'] as $priority)
                                            <option value="{{ $priority->name }}" {{ in_array($priority->name, $filters['priority'] ?? []) ? 'selected' : '' }}>
                                                {{ $priority->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Office Filter -->
                                <div>
                                    <label class="block text-sm font-medium mb-1">Department</label>
                                    <select name="office[]" multiple class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" size="3">
                                        @foreach($filterOptions['offices'] as $office)
                                            <option value="{{ $office->id }}" {{ in_array($office->id, $filters['office'] ?? []) ? 'selected' : '' }}>
                                                {{ $office->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Assignee Filter (Agents/Admins only) -->
                                @if(auth()->user()->isAgent() || auth()->user()->isAdmin())
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Assigned To</label>
                                        <select name="assignee[]" multiple class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" size="3">
                                            @foreach($filterOptions['assignees'] as $assignee)
                                                <option value="{{ $assignee->id }}" {{ in_array($assignee->id, $filters['assignee'] ?? []) ? 'selected' : '' }}>
                                                    {{ $assignee->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <!-- Date Range -->
                                <div>
                                    <label class="block text-sm font-medium mb-1">From Date</label>
                                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" 
                                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">To Date</label>
                                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" 
                                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                </div>
                            </div>

                            <!-- Sort Options -->
                            <div class="flex gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Sort By</label>
                                    <select name="sort_by" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                        <option value="relevance" {{ $sortBy === 'relevance' ? 'selected' : '' }}>Relevance</option>
                                        <option value="created_at" {{ $sortBy === 'created_at' ? 'selected' : '' }}>Date Created</option>
                                        <option value="updated_at" {{ $sortBy === 'updated_at' ? 'selected' : '' }}>Last Updated</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Order</label>
                                    <select name="sort_order" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                        <option value="desc" {{ $sortOrder === 'desc' ? 'selected' : '' }}>Descending</option>
                                        <option value="asc" {{ $sortOrder === 'asc' ? 'selected' : '' }}>Ascending</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Filter Actions -->
                            <div class="flex gap-2 mt-4">
                                <button type="button" onclick="clearFilters()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded text-sm">
                                    Clear Filters
                                </button>
                                <button type="button" onclick="saveCurrentSearch()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                                    Save Search
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="mb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Found {{ ($results['tickets']->total() ?? 0) + ($results['faqs']->total() ?? 0) }} results for "{{ $query }}"
                        </p>
                    </div>

                    @if(isset($results['tickets']) && $results['tickets']->count() > 0)
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold mb-4">Tickets</h3>
                            <div class="space-y-4">
                                @foreach($results['tickets'] as $ticket)
                                    <div class="border dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="block">
                                            <h4 class="font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                                {{ $ticket->subject }}
                                            </h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                {{ Str::limit(strip_tags($ticket->content), 150) }}
                                            </p>
                                            <div class="flex gap-4 mt-2 text-xs text-gray-500 dark:text-gray-500">
                                                <span>{{ $ticket->office->name }}</span>
                                                <span>{{ $ticket->status->name }}</span>
                                                <span>{{ $ticket->created_at->format('M d, Y') }}</span>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4">
                                {{ $results['tickets']->appends(['q' => $query, 'type' => $type])->links() }}
                            </div>
                        </div>
                    @endif

                    @if(isset($results['faqs']) && $results['faqs']->count() > 0)
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold mb-4">FAQs</h3>
                            <div class="space-y-4">
                                @foreach($results['faqs'] as $faq)
                                    <div class="border dark:border-gray-700 rounded-lg p-4">
                                        <h4 class="font-semibold">{{ $faq->question }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            {{ Str::limit(strip_tags($faq->answer), 200) }}
                                        </p>
                                        @if($faq->office)
                                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                                                Category: {{ $faq->office->name }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4">
                                {{ $results['faqs']->appends(['q' => $query, 'type' => $type])->links() }}
                            </div>
                        </div>
                    @endif

                    @if((isset($results['tickets']) && $results['tickets']->count() === 0) && 
                        (isset($results['faqs']) && $results['faqs']->count() === 0))
                        <p class="text-gray-500 dark:text-gray-400">No results found for your search.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Save Search Modal -->
    <div id="save-search-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Save Search</h3>
                <form id="save-search-form">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name</label>
                        <input type="text" id="search-name" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description (Optional)</label>
                        <textarea id="search-description" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"></textarea>
                    </div>
                    @if(auth()->user()->isAgent() || auth()->user()->isAdmin())
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" id="search-public" class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Make this search public (accessible by all agents)</span>
                            </label>
                        </div>
                    @endif
                    <div class="flex gap-2">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Save
                        </button>
                        <button type="button" onclick="closeSaveSearchModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Advanced filters visibility
        function toggleAdvancedFilters() {
            const filters = document.getElementById('advanced-filters');
            const toggle = document.getElementById('filters-toggle');
            
            if (filters.classList.contains('hidden')) {
                filters.classList.remove('hidden');
                toggle.textContent = 'Hide';
            } else {
                filters.classList.add('hidden');
                toggle.textContent = 'Advanced';
            }
        }

        // Saved searches visibility
        function toggleSavedSearches() {
            const list = document.getElementById('saved-searches-list');
            const toggle = document.getElementById('saved-searches-toggle');
            
            if (list.classList.contains('hidden')) {
                list.classList.remove('hidden');
                toggle.textContent = 'Hide';
            } else {
                list.classList.add('hidden');
                toggle.textContent = 'Show';
            }
        }

        // Clear all filters
        function clearFilters() {
            const form = document.getElementById('search-form');
            const inputs = form.querySelectorAll('input[type="date"], select[multiple]');
            inputs.forEach(input => {
                if (input.type === 'date') {
                    input.value = '';
                } else if (input.multiple) {
                    Array.from(input.options).forEach(option => option.selected = false);
                }
            });
            
            // Reset sort options
            document.querySelector('select[name="sort_by"]').value = 'relevance';
            document.querySelector('select[name="sort_order"]').value = 'desc';
        }

        // Save current search
        function saveCurrentSearch() {
            document.getElementById('save-search-modal').classList.remove('hidden');
        }

        // Close save search modal
        function closeSaveSearchModal() {
            document.getElementById('save-search-modal').classList.add('hidden');
            document.getElementById('save-search-form').reset();
        }

        // Handle save search form submission
        document.getElementById('save-search-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = document.getElementById('search-form');
            const formData = new FormData(form);
            const searchParams = Object.fromEntries(formData.entries());
            
            // Handle multiple select values
            const multiSelects = form.querySelectorAll('select[multiple]');
            multiSelects.forEach(select => {
                const values = Array.from(select.selectedOptions).map(option => option.value);
                if (values.length > 0) {
                    searchParams[select.name.replace('[]', '')] = values;
                }
            });

            const saveData = {
                name: document.getElementById('search-name').value,
                description: document.getElementById('search-description').value,
                search_params: searchParams,
                is_public: document.getElementById('search-public')?.checked || false,
            };

            try {
                const response = await fetch('{{ route('search.save') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify(saveData),
                });

                const result = await response.json();
                
                if (result.success) {
                    alert('Search saved successfully!');
                    closeSaveSearchModal();
                    location.reload(); // Refresh to show new saved search
                } else {
                    alert('Error saving search: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                alert('Error saving search: ' + error.message);
            }
        });

        // Delete saved search
        async function deleteSavedSearch(searchId) {
            if (!confirm('Are you sure you want to delete this saved search?')) {
                return;
            }

            try {
                const response = await fetch(`{{ url('search/saved') }}/${searchId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                });

                const result = await response.json();
                
                if (result.success) {
                    location.reload(); // Refresh to remove deleted search
                } else {
                    alert('Error deleting search: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                alert('Error deleting search: ' + error.message);
            }
        }

        // Show advanced filters if any filters are applied
        document.addEventListener('DOMContentLoaded', function() {
            const hasFilters = {{ !empty(array_filter($filters)) ? 'true' : 'false' }};
            if (hasFilters) {
                document.getElementById('filters-toggle').textContent = 'Hide';
            }
        });
    </script>
</x-app-layout>