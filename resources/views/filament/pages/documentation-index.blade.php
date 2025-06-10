@push('styles')
    <!-- GitHub-style syntax highlighting themes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css" media="(prefers-color-scheme: light)">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css" media="(prefers-color-scheme: dark)">
    
    <style>
        /* GitHub-style markdown rendering */
        .prose {
            @apply text-gray-900 dark:text-gray-100;
            max-width: none;
        }
        
        /* Headers with GitHub-style underlines */
        .prose h1 {
            @apply text-3xl font-bold border-b border-gray-200 dark:border-gray-700 pb-2 mb-4;
        }
        .prose h2 {
            @apply text-2xl font-semibold border-b border-gray-200 dark:border-gray-700 pb-1 mb-3 mt-6;
        }
        .prose h3 {
            @apply text-xl font-semibold mb-2 mt-5;
        }
        .prose h4 {
            @apply text-lg font-semibold mb-2 mt-4;
        }
        .prose h5, .prose h6 {
            @apply text-base font-semibold mb-2 mt-3;
        }
        
        /* Code blocks with GitHub-style appearance */
        .prose pre {
            @apply bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4 overflow-x-auto text-sm leading-relaxed;
            font-family: 'SFMono-Regular', 'Consolas', 'Liberation Mono', 'Menlo', monospace;
        }
        
        .prose pre code {
            @apply bg-transparent p-0 text-gray-900 dark:text-gray-100;
            font-size: inherit;
        }
        
        /* Inline code with GitHub styling */
        .prose code {
            @apply bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded text-sm font-mono;
            color: #d73a49;
        }
        
        .prose code:where(:not(.hljs)) {
            @apply text-red-600 dark:text-red-400;
        }
        
        /* Task lists with proper checkboxes */
        .prose ul.task-list {
            @apply list-none pl-0;
        }
        
        .prose .task-list-item {
            @apply flex items-start gap-2 mb-1;
        }
        
        .prose .task-list-item input[type="checkbox"] {
            @apply mt-1 mr-2 accent-green-600;
        }
        
        .prose .task-list-item input[type="checkbox"]:checked + span {
            @apply text-green-600 dark:text-green-400;
        }
        
        /* Tables with GitHub styling */
        .prose table {
            @apply w-full border-collapse border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden;
        }
        
        .prose th {
            @apply bg-gray-50 dark:bg-gray-800 font-semibold px-4 py-2 border border-gray-300 dark:border-gray-600;
        }
        
        .prose td {
            @apply px-4 py-2 border border-gray-300 dark:border-gray-600;
        }
        
        .prose tr:nth-child(even) {
            @apply bg-gray-25 dark:bg-gray-900/50;
        }
        
        /* Blockquotes */
        .prose blockquote {
            @apply border-l-4 border-blue-500 pl-4 py-2 bg-blue-50 dark:bg-blue-900/20 rounded-r-lg;
        }
        
        /* Links */
        .prose a {
            @apply text-blue-600 dark:text-blue-400 hover:underline;
        }
        
        /* Lists */
        .prose ul {
            @apply list-disc ml-6 mb-4;
        }
        
        .prose ol {
            @apply list-decimal ml-6 mb-4;
        }
        
        .prose li {
            @apply mb-1;
        }
        
        /* Horizontal rules */
        .prose hr {
            @apply border-gray-300 dark:border-gray-700 my-6;
        }
        
        /* Highlight.js overrides for better GitHub appearance */
        .prose .hljs {
            @apply bg-transparent;
        }
        
        /* Enhance specific syntax highlighting elements */
        .prose .hljs-keyword {
            @apply text-purple-600 dark:text-purple-400 font-semibold;
        }
        
        .prose .hljs-string {
            @apply text-green-700 dark:text-green-300;
        }
        
        .prose .hljs-comment {
            @apply text-gray-500 dark:text-gray-400 italic;
        }
        
        .prose .hljs-number {
            @apply text-blue-600 dark:text-blue-400;
        }
        
        .prose .hljs-variable {
            @apply text-red-600 dark:text-red-400;
        }
        
        .prose .hljs-function {
            @apply text-blue-600 dark:text-blue-400;
        }
        
        /* Dark mode specific enhancements */
        @media (prefers-color-scheme: dark) {
            .prose pre {
                @apply bg-gray-900 border-gray-700;
            }
            
            .prose code:where(:not(.hljs)) {
                @apply bg-gray-800 text-pink-400;
            }
        }
    </style>
@endpush

@push('scripts')
    <!-- Initialize highlight.js for dynamic syntax highlighting -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/bash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/sql.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/json.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/yaml.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            hljs.highlightAll();
            
            // Add copy buttons to code blocks
            document.querySelectorAll('pre code').forEach((block) => {
                const button = document.createElement('button');
                button.className = 'absolute top-2 right-2 px-2 py-1 text-xs bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors';
                button.innerHTML = 'Copy';
                
                button.addEventListener('click', () => {
                    navigator.clipboard.writeText(block.textContent).then(() => {
                        button.innerHTML = 'Copied!';
                        setTimeout(() => {
                            button.innerHTML = 'Copy';
                        }, 2000);
                    });
                });
                
                block.parentElement.style.position = 'relative';
                block.parentElement.appendChild(button);
            });
        });
    </script>
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