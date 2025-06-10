@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css" media="(prefers-color-scheme: dark)">
    <style>
        .prose pre {
            @apply bg-gray-100 dark:bg-gray-800 rounded-lg p-4 overflow-x-auto;
        }
        .prose code {
            @apply bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded text-sm;
        }
        .prose pre code {
            @apply bg-transparent p-0;
        }
        .prose table {
            @apply w-full border-collapse border border-gray-300 dark:border-gray-600;
        }
        .prose th, .prose td {
            @apply border border-gray-300 dark:border-gray-600 px-4 py-2;
        }
        .prose th {
            @apply bg-gray-50 dark:bg-gray-700 font-semibold;
        }
        .prose ul.task-list {
            @apply list-none pl-0;
        }
        .prose .task-list-item {
            @apply flex items-center gap-2;
        }
        .prose .task-list-item input[type="checkbox"] {
            @apply mr-2;
        }
        .prose blockquote {
            @apply border-l-4 border-blue-500 pl-4 italic text-gray-600 dark:text-gray-400;
        }
        .prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 {
            @apply scroll-mt-16;
        }
        .prose h1 {
            @apply text-3xl font-bold border-b border-gray-200 dark:border-gray-700 pb-2;
        }
        .prose h2 {
            @apply text-2xl font-semibold border-b border-gray-200 dark:border-gray-700 pb-1;
        }
        .prose h3 {
            @apply text-xl font-semibold;
        }
        .prose .hljs {
            @apply bg-transparent;
        }
    </style>
@endpush

<x-filament-panels::page>
    @php
        $doc = $this->getDocumentation();
    @endphp

    @if($doc)
        <x-filament::section>
            <div class="prose prose-lg max-w-none dark:prose-invert prose-headings:text-gray-900 dark:prose-headings:text-gray-100 prose-p:text-gray-700 dark:prose-p:text-gray-300 prose-a:text-blue-600 dark:prose-a:text-blue-400 prose-strong:text-gray-900 dark:prose-strong:text-gray-100">
                {!! $doc->rendered_content !!}
            </div>
        </x-filament::section>
    @else
        <x-filament::section>
            <div class="text-center py-12">
                <x-heroicon-o-document-text class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">Documentation not found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">The requested documentation could not be loaded.</p>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>