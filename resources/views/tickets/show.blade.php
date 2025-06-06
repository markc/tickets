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
                            <div class="space-y-2 text-sm">
                                <div><span class="font-medium">Created by:</span> {{ $ticket->creator->name }}</div>
                                <div><span class="font-medium">Department:</span> {{ $ticket->office->name }}</div>
                                <div><span class="font-medium">Created:</span> {{ $ticket->created_at->format('M j, Y \a\t g:i A') }}</div>
                                @if($ticket->assignedTo)
                                    <div><span class="font-medium">Assigned to:</span> {{ $ticket->assignedTo->name }}</div>
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
            @if($ticket->replies->count() > 0)
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Replies</h3>
                    @foreach($ticket->replies as $reply)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <span class="font-medium">{{ $reply->user->name }}</span>
                                        @if($reply->user->isAgent() || $reply->user->isAdmin())
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 ml-2">
                                                {{ $reply->user->isAdmin() ? 'Admin' : 'Agent' }}
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-sm text-gray-500">{{ $reply->created_at->format('M j, Y \a\t g:i A') }}</span>
                                </div>
                                <div class="prose max-w-none">
                                    {!! nl2br(e($reply->message)) !!}
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
                    <form method="POST" action="{{ route('tickets.reply', $ticket->uuid) }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="message" :value="__('Message')" />
                            <textarea id="message" name="message" rows="4" 
                                     class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                                     required>{{ old('message') }}</textarea>
                            <x-input-error :messages="$errors->get('message')" class="mt-2" />
                        </div>

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
</x-app-layout>