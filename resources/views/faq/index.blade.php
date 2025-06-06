<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Frequently Asked Questions') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Search and Filter -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('faq.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="search" :value="__('Search FAQs')" />
                                <x-text-input id="search" class="block mt-1 w-full" type="text" name="search" 
                                             :value="$search" placeholder="Search questions and answers..." />
                            </div>
                            <div>
                                <x-input-label for="office" :value="__('Department (optional)')" />
                                <select id="office" name="office" 
                                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">All Departments</option>
                                    @foreach($offices as $office)
                                        <option value="{{ $office->id }}" {{ $officeId == $office->id ? 'selected' : '' }}>
                                            {{ $office->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <x-primary-button>
                                {{ __('Search') }}
                            </x-primary-button>
                            @if($search || $officeId)
                                <a href="{{ route('faq.index') }}" class="text-gray-600 hover:text-gray-800">
                                    Clear filters
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- FAQs -->
            @if($faqs->count() > 0)
                <div class="space-y-4">
                    @foreach($faqs as $faq)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <details class="group">
                                    <summary class="flex justify-between items-center cursor-pointer list-none">
                                        <h3 class="text-lg font-medium text-gray-900 group-open:text-blue-600">
                                            {{ $faq->question }}
                                        </h3>
                                        <div class="flex items-center space-x-2">
                                            @if($faq->office)
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    {{ $faq->office->name }}
                                                </span>
                                            @endif
                                            <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" 
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </div>
                                    </summary>
                                    <div class="mt-4 text-gray-700 prose max-w-none">
                                        {!! nl2br(e($faq->answer)) !!}
                                    </div>
                                </details>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $faqs->appends(request()->query())->links() }}
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No FAQs found</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            @if($search || $officeId)
                                Try adjusting your search criteria or browse all FAQs.
                            @else
                                No frequently asked questions are available at the moment.
                            @endif
                        </p>
                        @if($search || $officeId)
                            <div class="mt-6">
                                <a href="{{ route('faq.index') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    View All FAQs
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Contact Support -->
            <div class="mt-8 bg-blue-50 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-center">
                    <h3 class="text-lg font-medium text-blue-900">Can't find what you're looking for?</h3>
                    <p class="mt-2 text-sm text-blue-700">
                        Our support team is here to help. Create a support ticket and we'll get back to you as soon as possible.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('tickets.create') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Create Support Ticket
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>