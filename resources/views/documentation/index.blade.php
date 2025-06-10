<x-documentation-layout>
    <div class="mx-auto max-w-6xl px-6 py-8">
        <!-- Hero Section -->
        <div class="text-center">
            <h1 class="text-4xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-6xl">
                TIKM Documentation
            </h1>
            <p class="mt-6 text-xl leading-8 text-gray-600 dark:text-gray-400">
                Complete guide to the TIKM customer support system. Everything you need to get started, manage tickets, and administer your helpdesk.
            </p>
        </div>

        <!-- Featured Documentation -->
        @if($featuredDocs->isNotEmpty())
        <div class="mt-16">
            <h2 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white mb-8">Get Started</h2>
            <div class="grid md:grid-cols-3 gap-6">
                @if($featuredDocs->has('quick-start'))
                <a href="{{ route('documentation.show', $featuredDocs['quick-start']) }}" 
                   class="group relative overflow-hidden rounded-lg bg-gradient-to-br from-primary-50 to-primary-100 p-6 transition-all hover:from-primary-100 hover:to-primary-200 dark:from-primary-950 dark:to-primary-900 dark:hover:from-primary-900 dark:hover:to-primary-800">
                    <div class="flex items-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary-600 text-white">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Quick Start</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Get TIKM running in 5 minutes</p>
                        </div>
                    </div>
                </a>
                @endif

                @if($featuredDocs->has('user-guide'))
                <a href="{{ route('documentation.show', $featuredDocs['user-guide']) }}" 
                   class="group relative overflow-hidden rounded-lg bg-gradient-to-br from-success-50 to-success-100 p-6 transition-all hover:from-success-100 hover:to-success-200 dark:from-success-950 dark:to-success-900 dark:hover:from-success-900 dark:hover:to-success-800">
                    <div class="flex items-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-success-600 text-white">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-950 dark:text-white">User Guide</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Learn how to use TIKM effectively</p>
                        </div>
                    </div>
                </a>
                @endif

                @if($featuredDocs->has('admin-guide'))
                <a href="{{ route('documentation.show', $featuredDocs['admin-guide']) }}" 
                   class="group relative overflow-hidden rounded-lg bg-gradient-to-br from-warning-50 to-warning-100 p-6 transition-all hover:from-warning-100 hover:to-warning-200 dark:from-warning-950 dark:to-warning-900 dark:hover:from-warning-900 dark:hover:to-warning-800">
                    <div class="flex items-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-warning-600 text-white">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Admin Guide</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">System administration</p>
                        </div>
                    </div>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- Documentation Categories -->
        <div class="mt-16">
            <h2 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white mb-8">All Documentation</h2>
            <div class="grid lg:grid-cols-2 gap-8">
                @foreach($documentsByCategory as $category => $documents)
                <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-center gap-x-3 mb-6">
                        @switch($category)
                            @case('overview')
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-600 text-white">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-950 dark:text-white">Overview</h3>
                                @break
                            @case('user')
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-600 text-white">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-950 dark:text-white">User Guide</h3>
                                @break
                            @case('admin')
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-warning-600 text-white">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-950 dark:text-white">Administration</h3>
                                @break
                            @case('api')
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-success-600 text-white">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-950 dark:text-white">API & Technical</h3>
                                @break
                            @case('deployment')
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-danger-600 text-white">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.75 17.25v-.228a4.5 4.5 0 00-.12-1.03l-2.268-9.64a3.375 3.375 0 00-3.285-2.602H7.923a3.375 3.375 0 00-3.285 2.602l-2.268 9.64a4.5 4.5 0 00-.12 1.03v.228m19.5 0a3 3 0 01-3 3H5.25a3 3 0 01-3-3m19.5 0a3 3 0 00-3-3H5.25a3 3 0 00-3 3m16.5 0h.008v.008h-.008v-.008zm-3 0h.008v.008h-.008v-.008z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-950 dark:text-white">Deployment</h3>
                                @break
                            @case('development')
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-info-600 text-white">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-950 dark:text-white">Development</h3>
                                @break
                            @default
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-600 text-white">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-950 dark:text-white">{{ ucfirst($category) }}</h3>
                        @endswitch
                    </div>
                    
                    <div class="space-y-3">
                        @foreach($documents as $document)
                        <a href="{{ route('documentation.show', $document) }}" 
                           class="group block rounded-lg p-3 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-sm font-medium text-gray-950 dark:text-white">{{ $document->title }}</h4>
                                    @if($document->description)
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $document->description }}</p>
                                    @endif
                                </div>
                                <svg class="h-4 w-4 text-gray-400 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Help Section -->
        <div class="mt-16 rounded-xl bg-gradient-to-r from-primary-50 to-primary-100 p-8 dark:from-primary-950 dark:to-primary-900">
            <div class="text-center">
                <h3 class="text-xl font-semibold text-gray-950 dark:text-white">Need Help?</h3>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Can't find what you're looking for? We're here to help.
                </p>
                <div class="mt-6 flex justify-center gap-4">
                    <a href="{{ route('faq.index') }}" 
                       class="fi-btn fi-color-gray fi-btn-color-gray fi-size-sm fi-btn-size-sm inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold shadow-sm ring-1 ring-gray-950/10 transition-all duration-75 hover:bg-gray-50 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:hover:bg-white/10">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"></path>
                        </svg>
                        Browse FAQ
                    </a>
                    @auth
                    <a href="{{ route('tickets.create') }}" 
                       class="fi-btn fi-color-primary fi-btn-color-primary fi-size-sm fi-btn-size-sm inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-75 hover:bg-primary-500 focus:ring-2 focus:ring-primary-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Create Support Ticket
                    </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</x-documentation-layout>