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
                    <form method="GET" action="{{ route('search') }}" class="mb-6">
                        <div class="flex gap-4">
                            <input type="text" name="q" value="{{ $query }}" 
                                class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600"
                                placeholder="Search tickets and FAQs...">
                            <select name="type" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="all" {{ $type === 'all' ? 'selected' : '' }}>All</option>
                                <option value="tickets" {{ $type === 'tickets' ? 'selected' : '' }}>Tickets Only</option>
                                <option value="faqs" {{ $type === 'faqs' ? 'selected' : '' }}>FAQs Only</option>
                            </select>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Search
                            </button>
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
</x-app-layout>