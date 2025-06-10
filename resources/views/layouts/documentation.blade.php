<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="fi fi-body h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($documentation) ? $documentation->title . ' - ' : '' }}{{ config('app.name', 'Laravel') }} Documentation</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

    <!-- Filament Styles -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @filamentStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="fi-body min-h-screen bg-gray-50 font-normal text-gray-950 antialiased dark:bg-gray-950 dark:text-white">
    <div class="fi-layout flex min-h-screen w-full" x-data="{ sidebarOpen: false }">
        <!-- Mobile sidebar backdrop -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden"
             @click="sidebarOpen = false"></div>

        <!-- Sidebar -->
        <aside class="fi-sidebar fi-sidebar-nav fixed inset-y-0 start-0 z-40 flex h-full w-64 flex-col overflow-hidden bg-white shadow-lg transition-transform duration-300 ease-in-out dark:bg-gray-900 lg:z-auto lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            <!-- Sidebar Header -->
            <div class="fi-sidebar-header flex h-16 items-center justify-between px-6 py-4">
                <a href="{{ route('documentation.index') }}" class="flex items-center gap-x-3">
                    <div class="fi-logo flex h-10 w-10 items-center justify-center rounded-lg bg-primary-600 text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">TIKM</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Documentation</span>
                    </div>
                </a>
                
                <!-- Close button for mobile -->
                <button type="button" 
                        class="fi-icon-btn relative flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-50 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:hover:bg-gray-800 dark:hover:text-gray-300 lg:hidden"
                        @click="sidebarOpen = false">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Search -->
            <div class="px-6 pb-4">
                <form action="{{ route('documentation.search') }}" method="GET" class="relative">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"></path>
                            </svg>
                        </div>
                        <input type="text" name="q" placeholder="Search documentation..." 
                               class="block w-full rounded-lg border-0 py-2 pl-10 pr-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/10 dark:placeholder:text-gray-500"
                               value="{{ request('q') }}">
                    </div>
                </form>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-6 py-4">
                <div class="space-y-6">
                    @foreach($navigation ?? [] as $categorySlug => $categoryData)
                    <div class="fi-sidebar-group">
                        <div class="fi-sidebar-group-label mb-2 text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {{ $categoryData['name'] }}
                        </div>
                        <ul class="space-y-1">
                            @foreach($categoryData['docs'] as $doc)
                            <li>
                                <a href="{{ route('documentation.show', $doc) }}" 
                                   class="fi-sidebar-item-button group flex w-full items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-75 hover:bg-gray-100 focus:bg-gray-100 focus:outline-none dark:hover:bg-white/5 dark:focus:bg-white/5 {{ isset($documentation) && $doc->id === $documentation->id ? 'bg-primary-50 text-primary-600 dark:bg-primary-400/10 dark:text-primary-400' : 'text-gray-700 hover:text-gray-950 dark:text-gray-200 dark:hover:text-white' }}">
                                    @if(isset($documentation) && $doc->id === $documentation->id)
                                    <div class="h-1.5 w-1.5 rounded-full bg-primary-600 dark:bg-primary-400"></div>
                                    @endif
                                    <span class="fi-sidebar-item-label truncate">{{ $doc->title }}</span>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endforeach
                </div>
            </nav>

            <!-- Footer -->
            <div class="border-t border-gray-200 p-4 dark:border-white/10">
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>TIKM v1.0</span>
                    @auth
                        @if(auth()->user()->role === 'admin')
                        <a href="{{ route('filament.admin.resources.documentations.index') }}" 
                           class="text-primary-600 hover:text-primary-500 dark:text-primary-400">
                            Manage Docs
                        </a>
                        @endif
                    @endauth
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="fi-main-ctn flex w-full flex-col lg:pl-64">
            <!-- Top Bar -->
            <header class="fi-topbar sticky top-0 z-10 flex h-16 items-center justify-between bg-white px-4 shadow-sm dark:bg-gray-900 sm:px-6 lg:px-8">
                <!-- Mobile menu button -->
                <button type="button" 
                        class="fi-topbar-mobile-menu-trigger -ml-0.5 -mt-0.5 inline-flex h-10 w-10 items-center justify-center rounded-md text-gray-500 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:text-gray-400 dark:hover:text-white lg:hidden"
                        @click="sidebarOpen = true">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <!-- Breadcrumbs -->
                <nav class="flex items-center space-x-2 text-sm">
                    <a href="{{ route('documentation.index') }}" 
                       class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        Documentation
                    </a>
                    @if(isset($documentation))
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8.25 4.5 7.5 7.5-7.5 7.5"></path>
                    </svg>
                    <span class="text-gray-900 dark:text-white capitalize">{{ $documentation->category }}</span>
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8.25 4.5 7.5 7.5-7.5 7.5"></path>
                    </svg>
                    <span class="text-gray-900 dark:text-white">{{ $documentation->title }}</span>
                    @endif
                </nav>

                <!-- Actions -->
                <div class="flex items-center gap-x-4">
                    @if(isset($documentation))
                        @auth
                            @if(auth()->user()->role === 'admin')
                            <a href="{{ route('filament.admin.resources.documentations.edit', $documentation) }}" 
                               class="fi-btn fi-color-gray fi-btn-color-gray fi-size-sm fi-btn-size-sm inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold shadow-sm ring-1 ring-gray-950/10 transition-all duration-75 hover:bg-gray-50 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:hover:bg-white/10">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"></path>
                                </svg>
                                Edit
                            </a>
                            @endif
                        @endauth
                    @endif

                    <!-- Back to app -->
                    <a href="{{ route('dashboard') }}" 
                       class="fi-btn fi-color-primary fi-btn-color-primary fi-size-sm fi-btn-size-sm inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-75 hover:bg-primary-500 focus:ring-2 focus:ring-primary-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to App
                    </a>
                </div>
            </header>

            <!-- Page Content -->
            <main class="fi-main flex-1">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewire('notifications')
    @filamentScripts
    @stack('scripts')
</body>
</html>