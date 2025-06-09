<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Merge Ticket') }} #{{ $ticket->uuid }}
            </h2>
            <a href="{{ route('tickets.show', $ticket) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Ticket
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Source Ticket Info -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Source Ticket (will be merged)</h3>
                        <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm">Will be merged</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Subject</p>
                            <p class="font-medium">{{ $ticket->subject }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Customer</p>
                            <p class="font-medium">{{ $ticket->creator->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                            <p class="font-medium">{{ $ticket->status->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Priority</p>
                            <p class="font-medium">{{ $ticket->priority->name }}</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Content</p>
                        <p class="mt-1">{{ Str::limit(strip_tags($ticket->content), 200) }}</p>
                    </div>
                </div>
            </div>

            <!-- Search for Target Ticket -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Search for Target Ticket</h3>
                    <div class="flex gap-4 mb-4">
                        <input type="text" id="search-input" placeholder="Search by subject, UUID, or content..." 
                            class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        <button onclick="searchTickets()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Search
                        </button>
                    </div>
                    <div id="search-results" class="hidden">
                        <h4 class="font-semibold mb-2">Search Results</h4>
                        <div id="search-results-list"></div>
                    </div>
                </div>
            </div>

            <!-- Suggested Merge Targets -->
            @if(count($suggestions) > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">Suggested Merge Targets</h3>
                        <div class="space-y-4">
                            @foreach($suggestions as $suggestion)
                                <div class="border dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" 
                                     onclick="selectTarget('{{ $suggestion['ticket']->uuid }}')">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-semibold text-blue-600 dark:text-blue-400">
                                                #{{ $suggestion['ticket']->uuid }}
                                            </h4>
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">
                                                {{ round($suggestion['similarity_score']) }}% match
                                            </span>
                                        </div>
                                        <span class="text-sm text-gray-500">{{ $suggestion['reason'] }}</span>
                                    </div>
                                    <p class="font-medium mb-1">{{ $suggestion['ticket']->subject }}</p>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm text-gray-600 dark:text-gray-400">
                                        <span>{{ $suggestion['ticket']->creator->name }}</span>
                                        <span>{{ $suggestion['ticket']->status->name }}</span>
                                        <span>{{ $suggestion['ticket']->priority->name }}</span>
                                        <span>{{ $suggestion['ticket']->created_at->format('M j, Y') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Recent Tickets -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Recent Tickets in {{ $ticket->office->name }}</h3>
                    <div class="space-y-3">
                        @foreach($recentTickets as $recentTicket)
                            <div class="border dark:border-gray-700 rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" 
                                 onclick="selectTarget('{{ $recentTicket->uuid }}')">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-blue-600 dark:text-blue-400">
                                            #{{ $recentTicket->uuid }} - {{ $recentTicket->subject }}
                                        </h4>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            <span>{{ $recentTicket->creator->name }}</span>
                                            <span>{{ $recentTicket->status->name }}</span>
                                            <span>{{ $recentTicket->priority->name }}</span>
                                            <span>{{ $recentTicket->updated_at->format('M j, Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Merge Confirmation Modal -->
    <div id="merge-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Confirm Ticket Merge</h3>
                
                <div id="target-ticket-info" class="mb-4"></div>
                
                <div id="merge-warnings" class="mb-4 hidden">
                    <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded p-3">
                        <h4 class="font-medium text-yellow-800 dark:text-yellow-200 mb-2">Warnings:</h4>
                        <ul id="warnings-list" class="list-disc list-inside text-sm text-yellow-700 dark:text-yellow-300"></ul>
                    </div>
                </div>

                <form id="merge-form" method="POST" action="{{ route('tickets.merge', $ticket) }}">
                    @csrf
                    <input type="hidden" id="target-ticket-uuid" name="target_ticket_uuid">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Reason for merge (optional)
                        </label>
                        <textarea name="reason" rows="3" 
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                            placeholder="Explain why these tickets should be merged..."></textarea>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            Merge Tickets
                        </button>
                        <button type="button" onclick="closeMergeModal()" 
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function searchTickets() {
            const query = document.getElementById('search-input').value;
            if (!query.trim()) return;

            fetch(`{{ route('tickets.merge.search', $ticket) }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    const resultsDiv = document.getElementById('search-results');
                    const resultsList = document.getElementById('search-results-list');
                    
                    if (data.results.length === 0) {
                        resultsList.innerHTML = '<p class="text-gray-500">No tickets found matching your search.</p>';
                    } else {
                        resultsList.innerHTML = data.results.map(result => `
                            <div class="border dark:border-gray-700 rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer mb-2" 
                                 onclick="selectTarget('${result.ticket.uuid}')">
                                <div class="flex items-center justify-between mb-1">
                                    <h4 class="font-medium text-blue-600 dark:text-blue-400">
                                        #${result.ticket.uuid} - ${result.ticket.subject}
                                    </h4>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">
                                        ${Math.round(result.similarity_score)}% match
                                    </span>
                                </div>
                                <div class="grid grid-cols-4 gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <span>${result.ticket.creator.name}</span>
                                    <span>${result.ticket.status.name}</span>
                                    <span>${result.ticket.priority.name}</span>
                                    <span>${new Date(result.ticket.created_at).toLocaleDateString()}</span>
                                </div>
                            </div>
                        `).join('');
                    }
                    
                    resultsDiv.classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Search failed:', error);
                    alert('Search failed. Please try again.');
                });
        }

        function selectTarget(targetUuid) {
            fetch(`{{ route('tickets.merge.preview', $ticket) }}?target_uuid=${targetUuid}`)
                .then(response => response.json())
                .then(data => {
                    if (!data.can_merge) {
                        alert('These tickets cannot be merged. They may be in different offices or already merged.');
                        return;
                    }

                    // Populate modal with target ticket info
                    document.getElementById('target-ticket-info').innerHTML = `
                        <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded p-3">
                            <h4 class="font-medium text-green-800 dark:text-green-200 mb-2">Target Ticket (will receive merged content)</h4>
                            <p><strong>UUID:</strong> #${data.target_ticket.uuid}</p>
                            <p><strong>Subject:</strong> ${data.target_ticket.subject}</p>
                            <p><strong>Customer:</strong> ${data.target_ticket.creator}</p>
                            <p><strong>Status:</strong> ${data.target_ticket.status}</p>
                            <p><strong>Priority:</strong> ${data.target_ticket.priority}</p>
                            <p><strong>Similarity:</strong> ${Math.round(data.similarity_score)}%</p>
                        </div>
                    `;

                    // Show warnings if any
                    if (data.warnings.length > 0) {
                        const warningsDiv = document.getElementById('merge-warnings');
                        const warningsList = document.getElementById('warnings-list');
                        warningsList.innerHTML = data.warnings.map(warning => `<li>${warning}</li>`).join('');
                        warningsDiv.classList.remove('hidden');
                    } else {
                        document.getElementById('merge-warnings').classList.add('hidden');
                    }

                    // Set the target UUID in the form
                    document.getElementById('target-ticket-uuid').value = targetUuid;

                    // Show the modal
                    document.getElementById('merge-modal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Preview failed:', error);
                    alert('Failed to load ticket preview. Please try again.');
                });
        }

        function closeMergeModal() {
            document.getElementById('merge-modal').classList.add('hidden');
        }

        // Allow Enter key to trigger search
        document.getElementById('search-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchTickets();
            }
        });
    </script>
</x-app-layout>