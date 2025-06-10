<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Documentation') }}
            </h2>
            <div class="flex items-center space-x-4">
                <form action="{{ route('documentation.search') }}" method="GET" class="flex items-center">
                    <input type="text" name="q" placeholder="Search documentation..." 
                           class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600">
                    <button type="submit" class="ml-2 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <x-heroicon-o-magnifying-glass class="h-4 w-4"/>
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <div class="text-center">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                            TIKM Documentation
                        </h1>
                        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
                            Complete guide to the TIKM customer support system
                        </p>
                    </div>

                    <!-- Featured Documentation -->
                    @if($featuredDocs->isNotEmpty())
                    <div class="grid md:grid-cols-3 gap-6 mb-8">
                        @if($featuredDocs->has('quick-start'))
                        <a href="{{ route('documentation.show', $featuredDocs['quick-start']) }}" 
                           class="block p-6 bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-blue-900 dark:to-indigo-900 rounded-lg hover:shadow-md transition-shadow">
                            <div class="flex items-center mb-3">
                                <x-heroicon-o-rocket-launch class="h-8 w-8 text-blue-600 dark:text-blue-400"/>
                                <h3 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">Quick Start</h3>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300">Get TIKM running in 5 minutes</p>
                        </a>
                        @endif

                        @if($featuredDocs->has('user-guide'))
                        <a href="{{ route('documentation.show', $featuredDocs['user-guide']) }}" 
                           class="block p-6 bg-gradient-to-br from-green-50 to-emerald-100 dark:from-green-900 dark:to-emerald-900 rounded-lg hover:shadow-md transition-shadow">
                            <div class="flex items-center mb-3">
                                <x-heroicon-o-user-group class="h-8 w-8 text-green-600 dark:text-green-400"/>
                                <h3 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">User Guide</h3>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300">Learn how to use TIKM effectively</p>
                        </a>
                        @endif

                        @if($featuredDocs->has('admin-guide'))
                        <a href="{{ route('documentation.show', $featuredDocs['admin-guide']) }}" 
                           class="block p-6 bg-gradient-to-br from-purple-50 to-violet-100 dark:from-purple-900 dark:to-violet-900 rounded-lg hover:shadow-md transition-shadow">
                            <div class="flex items-center mb-3">
                                <x-heroicon-o-cog-6-tooth class="h-8 w-8 text-purple-600 dark:text-purple-400"/>
                                <h3 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">Admin Guide</h3>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300">System administration</p>
                        </a>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Documentation Categories -->
            <div class="grid lg:grid-cols-2 gap-8">
                @foreach($documentsByCategory as $category => $documents)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            @switch($category)
                                @case('overview')
                                    <x-heroicon-o-information-circle class="h-6 w-6 mr-2 text-gray-600"/>
                                    Overview
                                    @break
                                @case('user')
                                    <x-heroicon-o-user-group class="h-6 w-6 mr-2 text-blue-600"/>
                                    User Guide
                                    @break
                                @case('admin')
                                    <x-heroicon-o-cog-6-tooth class="h-6 w-6 mr-2 text-amber-600"/>
                                    Administration
                                    @break
                                @case('api')
                                    <x-heroicon-o-code-bracket class="h-6 w-6 mr-2 text-green-600"/>
                                    API & Technical
                                    @break
                                @case('deployment')
                                    <x-heroicon-o-server class="h-6 w-6 mr-2 text-red-600"/>
                                    Deployment
                                    @break
                                @case('development')
                                    <x-heroicon-o-wrench-screwdriver class="h-6 w-6 mr-2 text-purple-600"/>
                                    Development
                                    @break
                                @default
                                    <x-heroicon-o-document-text class="h-6 w-6 mr-2 text-gray-600"/>
                                    {{ ucfirst($category) }}
                            @endswitch
                        </h2>
                        
                        <div class="space-y-3">
                            @foreach($documents as $document)
                            <a href="{{ route('documentation.show', $document) }}" 
                               class="block p-3 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex items-start">
                                    <div class="flex-1">
                                        <h3 class="font-medium text-gray-900 dark:text-white">{{ $document->title }}</h3>
                                        @if($document->description)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $document->description }}</p>
                                        @endif
                                    </div>
                                    <x-heroicon-o-arrow-right class="h-4 w-4 text-gray-400 ml-2 flex-shrink-0"/>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Help Section -->
            <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-100 dark:from-blue-900 dark:to-indigo-900 rounded-lg p-6">
                <div class="text-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Need Help?</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                        Can't find what you're looking for? We're here to help.
                    </p>
                    <div class="flex justify-center space-x-4">
                        <a href="{{ route('faq.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 dark:bg-blue-800 dark:text-blue-100 dark:hover:bg-blue-700">
                            <x-heroicon-o-question-mark-circle class="h-4 w-4 mr-2"/>
                            Browse FAQ
                        </a>
                        @auth
                        <a href="{{ route('tickets.create') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            <x-heroicon-o-plus class="h-4 w-4 mr-2"/>
                            Create Support Ticket
                        </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>