<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Ticket #{{ substr($ticket->uuid, 0, 8) }}
            </h2>
            <a href="{{ route('tickets.index') }}" class="text-blue-600 hover:text-blue-900">
                ← Back to Tickets
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Ticket Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-4">{{ $ticket->subject }}</h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex items-center">
                                    <span class="font-medium mr-2">Created by:</span> 
                                    <x-user-avatar :user="$ticket->creator" size="xs" class="mr-2" />
                                    <span>{{ $ticket->creator->name }}</span>
                                    <span class="text-xs text-gray-500 ml-1">({{ ucfirst($ticket->creator->role) }})</span>
                                </div>
                                <div><span class="font-medium">Department:</span> {{ $ticket->office->name }}</div>
                                <div><span class="font-medium">Created:</span> {{ $ticket->created_at->format('M j, Y \a\t g:i A') }}</div>
                                @if($ticket->assignedTo)
                                    <div class="flex items-center">
                                        <span class="font-medium mr-2">Assigned to:</span> 
                                        <x-user-avatar :user="$ticket->assignedTo" size="xs" class="mr-2" />
                                        <span>{{ $ticket->assignedTo->name }}</span>
                                        <span class="text-xs text-gray-500 ml-1">({{ ucfirst($ticket->assignedTo->role) }})</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div>
                                <span class="font-medium">Status:</span>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ml-2" 
                                      style="background-color: {{ $ticket->status->color }}20; color: {{ $ticket->status->color }};">
                                    {{ $ticket->status->name }}
                                </span>
                            </div>
                            <div>
                                <span class="font-medium">Priority:</span>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ml-2" 
                                      style="background-color: {{ $ticket->priority->color }}20; color: {{ $ticket->priority->color }};">
                                    {{ $ticket->priority->name }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h4 class="font-medium mb-2">Description:</h4>
                        <div class="bg-gray-50 p-4 rounded-md">
                            {!! nl2br(e($ticket->content)) !!}
                        </div>
                    </div>

                    @if($ticket->attachments->count() > 0)
                        <div class="mt-6">
                            <h4 class="font-medium mb-2">Attachments:</h4>
                            <div class="space-y-2">
                                @foreach($ticket->attachments as $attachment)
                                    <div class="flex items-center space-x-2 text-sm">
                                        <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                        <a href="{{ Storage::url($attachment->path) }}" 
                                           class="text-blue-600 hover:text-blue-900" target="_blank">
                                            {{ $attachment->filename }}
                                        </a>
                                        <span class="text-gray-500">({{ number_format($attachment->size / 1024, 1) }} KB)</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Replies -->
            @php
                $replies = auth()->user()->isCustomer() ? $ticket->publicReplies : $ticket->replies;
            @endphp
            @if($replies->count() > 0)
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Replies</h3>
                    @foreach($replies as $reply)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg {{ $reply->is_internal ? 'border-l-4 border-yellow-400' : '' }}">
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex items-center">
                                        <x-user-avatar :user="$reply->user" size="sm" class="mr-3" />
                                        <div>
                                            <span class="font-medium">{{ $reply->user->name }}</span>
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ml-2" 
                                                  style="background-color: #{{ $reply->user->getAvatarBackgroundColor() }}20; color: #{{ $reply->user->getAvatarBackgroundColor() }};">
                                                {{ ucfirst($reply->user->role) }}
                                            </span>
                                            @if($reply->is_internal && !auth()->user()->isCustomer())
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ml-1 bg-yellow-100 text-yellow-800">
                                                    Internal Note
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="text-sm text-gray-500">{{ $reply->created_at->format('M j, Y \a\t g:i A') }}</span>
                                </div>
                                <div class="prose max-w-none">
                                    {!! nl2br(e($reply->content)) !!}
                                </div>
                                @if($reply->attachments->count() > 0)
                                    <div class="mt-4">
                                        <h5 class="font-medium mb-2">Attachments:</h5>
                                        <div class="space-y-1">
                                            @foreach($reply->attachments as $attachment)
                                                <div class="flex items-center space-x-2 text-sm">
                                                    <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    <a href="{{ Storage::url($attachment->path) }}" 
                                                       class="text-blue-600 hover:text-blue-900" target="_blank">
                                                        {{ $attachment->filename }}
                                                    </a>
                                                    <span class="text-gray-500">({{ number_format($attachment->size / 1024, 1) }} KB)</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Reply Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Add Reply</h3>
                    
                    <!-- Canned Responses for Agents/Admins -->
                    @if(!auth()->user()->isCustomer())
                        <div class="mb-6 p-4 bg-blue-50 rounded-lg" id="canned-responses-section">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-semibold text-blue-900">Canned Responses</h4>
                                <button type="button" 
                                        onclick="toggleCannedResponses()" 
                                        class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                    <span id="toggle-text">Show Templates</span>
                                </button>
                            </div>
                            
                            <div id="canned-responses-interface" class="hidden space-y-3">
                                <div class="flex gap-3">
                                    <input type="text" 
                                           id="response-search" 
                                           placeholder="Search templates..." 
                                           class="flex-1 text-sm border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500">
                                    <select id="response-category" 
                                            class="text-sm border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">All Categories</option>
                                    </select>
                                </div>
                                
                                <div id="responses-list" class="max-h-48 overflow-y-auto space-y-2">
                                    <!-- Responses will be loaded here -->
                                </div>
                                
                                <div id="response-preview" class="hidden p-3 bg-white border rounded-md">
                                    <h5 class="text-sm font-medium mb-2">Preview:</h5>
                                    <div id="preview-content" class="text-sm text-gray-700 whitespace-pre-wrap"></div>
                                    <div class="mt-2 flex gap-2">
                                        <button type="button" 
                                                onclick="insertCannedResponse()" 
                                                class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                                            Use Template
                                        </button>
                                        <button type="button" 
                                                onclick="hideCannedPreview()" 
                                                class="px-3 py-1 bg-gray-300 text-gray-700 text-xs rounded hover:bg-gray-400">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('tickets.reply', $ticket->uuid) }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="message" :value="__('Message')" />
                            <textarea id="message" name="message" rows="4" 
                                     class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                                     required>{{ old('message') }}</textarea>
                            <x-input-error :messages="$errors->get('message')" class="mt-2" />
                        </div>

                        @if(!auth()->user()->isCustomer())
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_internal" value="1" 
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                                    <span class="ml-2 text-sm font-medium text-gray-700">Internal Note</span>
                                    <span class="ml-1 text-xs text-gray-500">(Only visible to agents and admins)</span>
                                </label>
                            </div>
                        @endif

                        <div class="mb-6">
                            <x-input-label for="reply_attachments" :value="__('Attachments (optional)')" />
                            <input id="reply_attachments" type="file" name="attachments[]" multiple 
                                   class="block mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" 
                                   accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt" />
                            <p class="mt-1 text-xs text-gray-500">Maximum file size: 10MB. Allowed types: JPG, PNG, PDF, DOC, DOCX, TXT</p>
                            <x-input-error :messages="$errors->get('attachments.*')" class="mt-2" />
                        </div>

                        <div class="flex justify-end">
                            <x-primary-button>
                                {{ __('Send Reply') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Timeline -->
            @if($ticket->timeline->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Activity Timeline</h3>
                        <div class="space-y-4">
                            @foreach($ticket->timeline->sortBy('created_at') as $event)
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm text-gray-900">
                                            <span class="font-medium">{{ $event->user->name }}</span>
                                            {{ $event->description }}
                                        </p>
                                        <p class="text-xs text-gray-500">{{ $event->created_at->format('M j, Y \a\t g:i A') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if(!auth()->user()->isCustomer())
        <script>
            let cannedResponses = [];
            let selectedResponse = null;
            let cannedResponsesVisible = false;

            async function toggleCannedResponses() {
                const interface_ = document.getElementById('canned-responses-interface');
                const toggleText = document.getElementById('toggle-text');
                
                if (cannedResponsesVisible) {
                    interface_.classList.add('hidden');
                    toggleText.textContent = 'Show Templates';
                    cannedResponsesVisible = false;
                } else {
                    if (cannedResponses.length === 0) {
                        await loadCannedResponses();
                    }
                    interface_.classList.remove('hidden');
                    toggleText.textContent = 'Hide Templates';
                    cannedResponsesVisible = true;
                }
            }

            async function loadCannedResponses() {
                try {
                    const response = await fetch('{{ route("api.canned-responses.index") }}');
                    const data = await response.json();
                    
                    cannedResponses = data.data;
                    
                    // Populate categories
                    const categorySelect = document.getElementById('response-category');
                    Object.entries(data.categories).forEach(([value, label]) => {
                        const option = document.createElement('option');
                        option.value = value;
                        option.textContent = label;
                        categorySelect.appendChild(option);
                    });
                    
                    displayCannedResponses(cannedResponses);
                    
                    // Add event listeners
                    document.getElementById('response-search').addEventListener('input', filterResponses);
                    document.getElementById('response-category').addEventListener('change', filterResponses);
                } catch (error) {
                    console.error('Error loading canned responses:', error);
                }
            }

            function displayCannedResponses(responses) {
                const listContainer = document.getElementById('responses-list');
                
                if (responses.length === 0) {
                    listContainer.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">No templates found</p>';
                    return;
                }
                
                listContainer.innerHTML = responses.map(response => `
                    <div class="response-item p-3 bg-white border rounded cursor-pointer hover:bg-blue-50 transition-colors"
                         data-response-id="${response.id}">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h6 class="text-sm font-medium text-gray-900">${response.title}</h6>
                                <p class="text-xs text-gray-500 mt-1 line-clamp-2">${response.content.substring(0, 100)}...</p>
                            </div>
                            <div class="flex flex-col items-end ml-3">
                                ${response.category ? `<span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">${response.category}</span>` : ''}
                                <span class="text-xs text-gray-400 mt-1">Used ${response.usage_count} times</span>
                            </div>
                        </div>
                    </div>
                `).join('');
                
                // Add click listeners
                document.querySelectorAll('.response-item').forEach(item => {
                    item.addEventListener('click', () => {
                        const responseId = item.dataset.responseId;
                        previewCannedResponse(responseId);
                    });
                });
            }

            function filterResponses() {
                const searchTerm = document.getElementById('response-search').value.toLowerCase();
                const category = document.getElementById('response-category').value;
                
                const filtered = cannedResponses.filter(response => {
                    const matchesSearch = !searchTerm || 
                        response.title.toLowerCase().includes(searchTerm) ||
                        response.content.toLowerCase().includes(searchTerm);
                    
                    const matchesCategory = !category || response.category === category;
                    
                    return matchesSearch && matchesCategory;
                });
                
                displayCannedResponses(filtered);
            }

            async function previewCannedResponse(responseId) {
                try {
                    const response = await fetch(`{{ url('api/canned-responses') }}/${responseId}/preview`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            ticket_id: '{{ $ticket->uuid }}'
                        })
                    });
                    
                    const data = await response.json();
                    selectedResponse = { id: responseId, content: data.data.processed_content };
                    
                    document.getElementById('preview-content').textContent = data.data.processed_content;
                    document.getElementById('response-preview').classList.remove('hidden');
                } catch (error) {
                    console.error('Error previewing response:', error);
                }
            }

            function insertCannedResponse() {
                if (selectedResponse) {
                    const messageTextarea = document.getElementById('message');
                    const currentContent = messageTextarea.value;
                    const newContent = currentContent ? currentContent + '\n\n' + selectedResponse.content : selectedResponse.content;
                    messageTextarea.value = newContent;
                    
                    // Mark the response as used
                    fetch(`{{ url('api/canned-responses') }}/${selectedResponse.id}/use`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            ticket_id: '{{ $ticket->uuid }}'
                        })
                    });
                    
                    hideCannedPreview();
                    toggleCannedResponses(); // Hide the interface
                }
            }

            function hideCannedPreview() {
                document.getElementById('response-preview').classList.add('hidden');
                selectedResponse = null;
            }
        </script>
    @endif
</x-app-layout>