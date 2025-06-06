<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Ticket') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="subject" :value="__('Subject')" />
                            <x-text-input id="subject" class="block mt-1 w-full" type="text" name="subject" 
                                         :value="old('subject')" required autofocus />
                            <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="office_id" :value="__('Department')" />
                            <select id="office_id" name="office_id" 
                                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">Select a department</option>
                                @foreach($offices as $office)
                                    <option value="{{ $office->id }}" {{ old('office_id') == $office->id ? 'selected' : '' }}>
                                        {{ $office->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('office_id')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="ticket_priority_id" :value="__('Priority')" />
                            <select id="ticket_priority_id" name="ticket_priority_id" 
                                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">Select priority</option>
                                @foreach($priorities as $priority)
                                    <option value="{{ $priority->id }}" {{ old('ticket_priority_id') == $priority->id ? 'selected' : '' }}>
                                        {{ $priority->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('ticket_priority_id')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="content" :value="__('Description')" />
                            <textarea id="content" name="content" rows="6" 
                                     class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                                     required>{{ old('content') }}</textarea>
                            <x-input-error :messages="$errors->get('content')" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="attachments" :value="__('Attachments (optional)')" />
                            <input id="attachments" type="file" name="attachments[]" multiple 
                                   class="block mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" 
                                   accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt" />
                            <p class="mt-1 text-xs text-gray-500">Maximum file size: 10MB. Allowed types: JPG, PNG, PDF, DOC, DOCX, TXT</p>
                            <x-input-error :messages="$errors->get('attachments.*')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end">
                            <a href="{{ route('tickets.index') }}" 
                               class="mr-4 px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Create Ticket') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>