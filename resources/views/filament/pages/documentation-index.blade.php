@push('styles')
    <!-- GitHub-style syntax highlighting themes with cache busting -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css?v={{ time() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css?v={{ time() }}" media="(prefers-color-scheme: dark)">
    
    <style>
        /* GitHub-accurate CSS variables */
        #documentation-content {
            --color-text-primary: #24292f;
            --color-text-secondary: #57606a;
            --color-border-primary: #d0d7de;
            --color-border-secondary: #eaecef;
            --color-code-bg: #f6f8fa;
            --color-code-text: #24292f;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
            font-size: 16px;
            line-height: 1.5;
            color: var(--color-text-primary);
            max-width: none;
        }
        
        @media (prefers-color-scheme: dark) {
            #documentation-content {
                --color-text-primary: #c9d1d9;
                --color-text-secondary: #8b949e;
                --color-border-primary: #30363d;
                --color-border-secondary: #21262d;
                --color-code-bg: #161b22;
                --color-code-text: #c9d1d9;
            }
        }
        
        html[class~="dark"] #documentation-content {
            --color-text-primary: #c9d1d9;
            --color-text-secondary: #8b949e;
            --color-border-primary: #30363d;
            --color-border-secondary: #21262d;
            --color-code-bg: #161b22;
            --color-code-text: #c9d1d9;
        }
        
        html[class~="dark"] .prose {
            color: #e6edf3;
        }
        
        /* Headers with exact GitHub styling */
        .prose h1 {
            font-size: 2em;
            font-weight: 600;
            line-height: 1.25;
            padding-bottom: 0.3em;
            border-bottom: 1px solid #d1d9e0;
            margin-bottom: 16px;
            margin-top: 24px;
            color: #1f2328;
        }
        
        html[class~="dark"] .prose h1 {
            border-bottom-color: #30363d;
            color: #e6edf3;
        }
        
        .prose h2 {
            font-size: 1.5em;
            font-weight: 600;
            line-height: 1.25;
            padding-bottom: 0.3em;
            border-bottom: 1px solid #d1d9e0;
            margin-bottom: 16px;
            margin-top: 24px;
            color: #1f2328;
        }
        
        html[class~="dark"] .prose h2 {
            border-bottom-color: #30363d;
            color: #e6edf3;
        }
        
        .prose h3 {
            font-size: 1.25em;
            font-weight: 600;
            line-height: 1.25;
            margin-bottom: 16px;
            margin-top: 24px;
            color: #1f2328;
        }
        
        html[class~="dark"] .prose h3 {
            color: #e6edf3;
        }
        
        .prose h4, .prose h5, .prose h6 {
            font-size: 1em;
            font-weight: 600;
            line-height: 1.25;
            margin-bottom: 16px;
            margin-top: 24px;
            color: #1f2328;
        }
        
        html[class~="dark"] .prose h4,
        html[class~="dark"] .prose h5,
        html[class~="dark"] .prose h6 {
            color: #e6edf3;
        }
        
        /* GitHub-exact code block styling */
        #documentation-content pre {
            margin-top: 0;
            margin-bottom: 16px;
            padding: 16px;
            overflow: auto;
            font-size: 85%;
            line-height: 1.45;
            background-color: var(--color-code-bg);
            border-radius: 6px;
            border: 1px solid var(--color-border-primary);
            position: relative;
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace;
        }
        
        #documentation-content pre code {
            padding: 0 !important;
            margin: 0 !important;
            background-color: transparent !important;
            border: 0 !important;
            font-size: 100% !important;
            color: var(--color-text-primary) !important;
            word-break: normal;
            white-space: pre;
            border-radius: 0 !important;
        }
        
        /* Force inline code styling with absolute maximum specificity */
        .fi-section .prose code:not(.hljs):not(pre code),
        .prose code:not(.hljs):not(pre code),
        div.prose code:not(.hljs):not(pre code),
        code:not(.hljs):not(pre code) {
            background-color: rgba(175, 184, 193, 0.2) !important;
            padding: 0.2em 0.4em !important;
            margin: 0 !important;
            font-size: 85% !important;
            border-radius: 6px !important;
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Consolas, "Liberation Mono", Menlo, monospace !important;
            color: #1f2328 !important;
            font-weight: 400 !important;
            border: none !important;
            box-shadow: none !important;
            text-decoration: none !important;
        }
        
        /* Force override for all possible contexts */
        * code:not(.hljs):not(pre code) {
            background-color: rgba(175, 184, 193, 0.2) !important;
            padding: 0.2em 0.4em !important;
            margin: 0 !important;
            font-size: 85% !important;
            border-radius: 6px !important;
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Consolas, "Liberation Mono", Menlo, monospace !important;
            color: #1f2328 !important;
            font-weight: 400 !important;
            border: none !important;
            box-shadow: none !important;
            text-decoration: none !important;
        }
        
        /* GitHub-exact inline code styling */
        #documentation-content code {
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace !important;
            font-size: 85% !important;
            padding: 0.2em 0.4em !important;
            margin: 0 !important;
            background-color: var(--color-code-bg) !important;
            border-radius: 6px !important;
            color: var(--color-code-text) !important;
            border: none !important;
        }
        
        /* Reset for code blocks */
        pre code {
            background-color: transparent !important;
            padding: 0 !important;
            border-radius: 0 !important;
            border: none !important;
            font-size: 100% !important;
        }
        
        /* Dark mode */
        html[class~="dark"] code {
            background-color: rgba(110, 118, 129, 0.4) !important;
            color: #f0f6fc !important;
            border-color: rgba(110, 118, 129, 0.4) !important;
        }
        
        html[class~="dark"] pre code {
            background-color: transparent !important;
            border: none !important;
        }
        
        /* Additional selectors for inline code with maximum specificity */
        div.prose p code,
        div.prose li code,
        div.prose td code,
        div.prose th code,
        div.prose h1 code,
        div.prose h2 code,
        div.prose h3 code,
        div.prose h4 code,
        div.prose h5 code,
        div.prose h6 code,
        .prose p code,
        .prose li code,
        .prose td code,
        .prose th code,
        .prose h1 code,
        .prose h2 code,
        .prose h3 code,
        .prose h4 code,
        .prose h5 code,
        .prose h6 code {
            background-color: rgba(175, 184, 193, 0.2) !important;
            padding: 0.2em 0.4em !important;
            margin: 0 !important;
            font-size: 85% !important;
            border-radius: 6px !important;
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Consolas, "Liberation Mono", Menlo, monospace !important;
            color: #1f2328 !important;
            font-weight: 400 !important;
            border: none !important;
            box-shadow: none !important;
        }
        
        /* Dark mode for inline code */
        html[class~="dark"] .prose code:not(.hljs):not(pre code),
        html[class~="dark"] .prose p code,
        html[class~="dark"] .prose li code,
        html[class~="dark"] .prose td code,
        html[class~="dark"] .prose th code,
        html[class~="dark"] .prose h1 code,
        html[class~="dark"] .prose h2 code,
        html[class~="dark"] .prose h3 code,
        html[class~="dark"] .prose h4 code,
        html[class~="dark"] .prose h5 code,
        html[class~="dark"] .prose h6 code {
            background-color: rgba(110, 118, 129, 0.4) !important;
            color: #e6edf3 !important;
        }
        
        /* Dark mode media query backup */
        @media (prefers-color-scheme: dark) {
            .prose code:not(.hljs):not(pre code),
            .prose p code,
            .prose li code,
            .prose td code,
            .prose th code,
            .prose h1 code,
            .prose h2 code,
            .prose h3 code,
            .prose h4 code,
            .prose h5 code,
            .prose h6 code {
                background-color: rgba(110, 118, 129, 0.4) !important;
                color: #e6edf3 !important;
            }
        }
        
        /* Ensure pre code doesn't get inline styling */
        .prose pre code {
            background-color: transparent !important;
            padding: 0 !important;
            border-radius: 0 !important;
            font-size: 100% !important;
        }
        
        /* Lists with exact GitHub styling */
        .prose ul, .prose ol {
            margin-bottom: 16px;
            padding-left: 2em;
        }
        
        .prose ul {
            list-style-type: disc;
        }
        
        .prose ol {
            list-style-type: decimal;
        }
        
        .prose li {
            margin-bottom: 0.25em;
        }
        
        .prose li > p {
            margin-bottom: 16px;
        }
        
        /* Task lists with proper checkboxes */
        .prose ul.task-list {
            list-style: none;
            padding-left: 0;
        }
        
        .prose .task-list-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 0.25em;
        }
        
        .prose .task-list-item input[type="checkbox"] {
            margin: 0.35em 0.5em 0.25em -1.6em;
            vertical-align: middle;
        }
        
        /* Tables with exact GitHub styling */
        .prose table {
            border-spacing: 0;
            border-collapse: collapse;
            margin-bottom: 16px;
            display: block;
            width: max-content;
            max-width: 100%;
            overflow: auto;
        }
        
        .prose th, .prose td {
            padding: 6px 13px;
            border: 1px solid #d1d9e0;
        }
        
        .prose th {
            font-weight: 600;
            background-color: #f6f8fa;
        }
        
        html[class~="dark"] .prose th {
            background-color: #161b22;
            border-color: #30363d;
        }
        
        html[class~="dark"] .prose td {
            border-color: #30363d;
        }
        
        /* Blockquotes with exact GitHub styling */
        .prose blockquote {
            margin: 0 0 16px 0;
            padding: 0 1em;
            color: #656d76;
            border-left: 0.25em solid #d1d9e0;
        }
        
        html[class~="dark"] .prose blockquote {
            color: #848d97;
            border-left-color: #30363d;
        }
        
        /* Links with exact GitHub styling */
        .prose a {
            color: #0969da;
            text-decoration: none;
        }
        
        .prose a:hover {
            text-decoration: underline;
        }
        
        html[class~="dark"] .prose a {
            color: #58a6ff;
        }
        
        /* Horizontal rules */
        .prose hr {
            height: 0.25em;
            padding: 0;
            margin: 24px 0;
            background-color: #d1d9e0;
            border: 0;
        }
        
        html[class~="dark"] .prose hr {
            background-color: #30363d;
        }
        
        /* Paragraphs */
        .prose p {
            margin-bottom: 16px;
        }
        
        /* Emphasis and strong */
        .prose strong {
            font-weight: 600;
        }
        
        /* Copy button styling to match GitHub */
        .copy-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #f6f8fa;
            border: 1px solid #d1d9e0;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 12px;
            line-height: 1.45;
            color: #24292f;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.2s;
        }
        
        .prose pre:hover .copy-btn {
            opacity: 1;
        }
        
        .copy-btn:hover {
            background: #f3f4f6;
            border-color: #afb8c1;
        }
        
        html[class~="dark"] .copy-btn {
            background: #21262d;
            border-color: #30363d;
            color: #f0f6fc;
        }
        
        html[class~="dark"] .copy-btn:hover {
            background: #30363d;
            border-color: #8b949e;
        }
        
        /* Enhanced GitHub Syntax Highlighting Colors */
        #documentation-content .hljs {
            background: transparent !important;
        }
        
        /* GitHub-exact syntax highlighting colors */
        #documentation-content .hljs-comment,
        #documentation-content .hljs-prolog,
        #documentation-content .hljs-doctype,
        #documentation-content .hljs-cdata {
            color: #6a737d !important;
        }
        
        #documentation-content .hljs-punctuation {
            color: #6a737d !important;
        }
        
        #documentation-content .hljs-property,
        #documentation-content .hljs-tag,
        #documentation-content .hljs-boolean,
        #documentation-content .hljs-number,
        #documentation-content .hljs-constant,
        #documentation-content .hljs-symbol,
        #documentation-content .hljs-deleted {
            color: #005cc5 !important;
        }
        
        #documentation-content .hljs-selector,
        #documentation-content .hljs-attr-name,
        #documentation-content .hljs-string,
        #documentation-content .hljs-char,
        #documentation-content .hljs-builtin,
        #documentation-content .hljs-inserted {
            color: #032f62 !important;
        }
        
        #documentation-content .hljs-operator,
        #documentation-content .hljs-entity,
        #documentation-content .hljs-url,
        #documentation-content .hljs-keyword {
            color: #d73a49 !important;
        }
        
        #documentation-content .hljs-function,
        #documentation-content .hljs-class-name {
            color: #6f42c1 !important;
        }
        
        #documentation-content .hljs-regex,
        #documentation-content .hljs-important,
        #documentation-content .hljs-variable {
            color: #e36209 !important;
        }
        
        /* Dark mode syntax highlighting */
        @media (prefers-color-scheme: dark) {
            #documentation-content .hljs-comment,
            #documentation-content .hljs-prolog,
            #documentation-content .hljs-doctype,
            #documentation-content .hljs-cdata,
            #documentation-content .hljs-punctuation {
                color: #8b949e !important;
            }
            
            #documentation-content .hljs-property,
            #documentation-content .hljs-tag,
            #documentation-content .hljs-constant,
            #documentation-content .hljs-symbol,
            #documentation-content .hljs-deleted {
                color: #79c0ff !important;
            }
            
            #documentation-content .hljs-selector,
            #documentation-content .hljs-attr-name,
            #documentation-content .hljs-string,
            #documentation-content .hljs-char,
            #documentation-content .hljs-builtin,
            #documentation-content .hljs-inserted {
                color: #a5d6ff !important;
            }
            
            #documentation-content .hljs-operator,
            #documentation-content .hljs-entity,
            #documentation-content .hljs-url,
            #documentation-content .hljs-keyword {
                color: #ff7b72 !important;
            }
            
            #documentation-content .hljs-function,
            #documentation-content .hljs-class-name {
                color: #d2a8ff !important;
            }
            
            #documentation-content .hljs-regex,
            #documentation-content .hljs-important,
            #documentation-content .hljs-variable {
                color: #ffa657 !important;
            }
        }
        
        html[class~="dark"] #documentation-content .hljs-comment,
        html[class~="dark"] #documentation-content .hljs-prolog,
        html[class~="dark"] #documentation-content .hljs-doctype,
        html[class~="dark"] #documentation-content .hljs-cdata,
        html[class~="dark"] #documentation-content .hljs-punctuation {
            color: #8b949e !important;
        }
        
        html[class~="dark"] #documentation-content .hljs-property,
        html[class~="dark"] #documentation-content .hljs-tag,
        html[class~="dark"] #documentation-content .hljs-constant,
        html[class~="dark"] #documentation-content .hljs-symbol,
        html[class~="dark"] #documentation-content .hljs-deleted {
            color: #79c0ff !important;
        }
        
        html[class~="dark"] #documentation-content .hljs-selector,
        html[class~="dark"] #documentation-content .hljs-attr-name,
        html[class~="dark"] #documentation-content .hljs-string,
        html[class~="dark"] #documentation-content .hljs-char,
        html[class~="dark"] #documentation-content .hljs-builtin,
        html[class~="dark"] #documentation-content .hljs-inserted {
            color: #a5d6ff !important;
        }
        
        html[class~="dark"] #documentation-content .hljs-operator,
        html[class~="dark"] #documentation-content .hljs-entity,
        html[class~="dark"] #documentation-content .hljs-url,
        html[class~="dark"] #documentation-content .hljs-keyword {
            color: #ff7b72 !important;
        }
        
        html[class~="dark"] #documentation-content .hljs-function,
        html[class~="dark"] #documentation-content .hljs-class-name {
            color: #d2a8ff !important;
        }
        
        html[class~="dark"] #documentation-content .hljs-regex,
        html[class~="dark"] #documentation-content .hljs-important,
        html[class~="dark"] #documentation-content .hljs-variable {
            color: #ffa657 !important;
        }
        
        /* Dark Mode Syntax Highlighting */
        @media (prefers-color-scheme: dark) {
            /* Keywords (if, class, function, etc.) */
            .prose .hljs-keyword,
            .prose .hljs-selector-tag,
            .prose .hljs-literal,
            .prose .hljs-section,
            .prose .hljs-link {
                color: #ff7b72;
            }
            
            /* Strings */
            .prose .hljs-string,
            .prose .hljs-attr {
                color: #a5d6ff;
            }
            
            /* Numbers */
            .prose .hljs-number,
            .prose .hljs-literal {
                color: #79c0ff;
            }
            
            /* Comments */
            .prose .hljs-comment,
            .prose .hljs-quote {
                color: #8b949e;
                font-style: italic;
            }
            
            /* Variables and function names */
            .prose .hljs-variable,
            .prose .hljs-title.function_,
            .prose .hljs-title {
                color: #d2a8ff;
            }
            
            /* Class names and types */
            .prose .hljs-title.class_,
            .prose .hljs-type {
                color: #ffa657;
            }
            
            /* Built-in functions and keywords */
            .prose .hljs-built_in,
            .prose .hljs-builtin-name {
                color: #79c0ff;
            }
            
            /* Operators */
            .prose .hljs-operator {
                color: #ff7b72;
            }
            
            /* Attributes and properties */
            .prose .hljs-attribute {
                color: #7ee787;
            }
            
            /* Meta and preprocessor */
            .prose .hljs-meta,
            .prose .hljs-meta .hljs-string {
                color: #79c0ff;
            }
            
            /* Template literals and special strings */
            .prose .hljs-template-tag,
            .prose .hljs-template-variable {
                color: #a5d6ff;
            }
        }
        
        /* Ensure dark mode is applied correctly */
        html[class~="dark"] .prose .hljs-keyword,
        html[class~="dark"] .prose .hljs-selector-tag,
        html[class~="dark"] .prose .hljs-literal,
        html[class~="dark"] .prose .hljs-section,
        html[class~="dark"] .prose .hljs-link {
            color: #ff7b72;
        }
        
        html[class~="dark"] .prose .hljs-string,
        html[class~="dark"] .prose .hljs-attr {
            color: #a5d6ff;
        }
        
        html[class~="dark"] .prose .hljs-number {
            color: #79c0ff;
        }
        
        html[class~="dark"] .prose .hljs-comment,
        html[class~="dark"] .prose .hljs-quote {
            color: #8b949e;
            font-style: italic;
        }
        
        html[class~="dark"] .prose .hljs-variable,
        html[class~="dark"] .prose .hljs-title.function_,
        html[class~="dark"] .prose .hljs-title {
            color: #d2a8ff;
        }
        
        html[class~="dark"] .prose .hljs-title.class_,
        html[class~="dark"] .prose .hljs-type {
            color: #ffa657;
        }
        
        html[class~="dark"] .prose .hljs-built_in,
        html[class~="dark"] .prose .hljs-builtin-name {
            color: #79c0ff;
        }
        
        html[class~="dark"] .prose .hljs-operator {
            color: #ff7b72;
        }
        
        html[class~="dark"] .prose .hljs-attribute {
            color: #7ee787;
        }
        
        html[class~="dark"] .prose .hljs-meta,
        html[class~="dark"] .prose .hljs-meta .hljs-string {
            color: #79c0ff;
        }
        
        html[class~="dark"] .prose .hljs-template-tag,
        html[class~="dark"] .prose .hljs-template-variable {
            color: #a5d6ff;
        }
    </style>
@endpush

@push('scripts')
    <!-- Initialize highlight.js for dynamic syntax highlighting with cache busting -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js?v={{ time() }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/php.min.js?v={{ time() }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/bash.min.js?v={{ time() }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/javascript.min.js?v={{ time() }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/sql.min.js?v={{ time() }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/json.min.js?v={{ time() }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/yaml.min.js?v={{ time() }}"></script>
    <script>
        // Wait for all scripts to load
        window.addEventListener('load', function() {
            console.log('Page loaded, initializing syntax highlighting...');
            console.log('hljs available:', typeof hljs !== 'undefined');
            
            if (typeof hljs !== 'undefined') {
                // Configure hljs to be less aggressive
                hljs.configure({
                    ignoreUnescapedHTML: true,
                    languages: ['php', 'bash', 'javascript', 'sql', 'json', 'yaml'],
                    throwUnescapedHTML: false
                });
                
                // Only highlight pre code blocks, not inline code
                const codeBlocks = document.querySelectorAll('#documentation-content pre code');
                console.log('Found code blocks:', codeBlocks.length);
                
                codeBlocks.forEach((block, index) => {
                    // Only highlight if it has a language class or is clearly a code block
                    if (block.className.includes('language-') || block.parentElement.tagName === 'PRE') {
                        console.log(`Highlighting block ${index + 1}:`, block.className);
                        hljs.highlightElement(block);
                    }
                });
                
                console.log('Syntax highlighting completed');
            } else {
                console.error('highlight.js not loaded!');
            }
            
            // Add GitHub-style copy buttons to code blocks only
            document.querySelectorAll('pre code').forEach((block) => {
                const button = document.createElement('button');
                button.className = 'copy-btn';
                button.innerHTML = 'Copy';
                button.setAttribute('aria-label', 'Copy to clipboard');
                
                button.addEventListener('click', () => {
                    navigator.clipboard.writeText(block.textContent).then(() => {
                        button.innerHTML = 'Copied!';
                        setTimeout(() => {
                            button.innerHTML = 'Copy';
                        }, 2000);
                    }).catch(() => {
                        // Fallback for browsers that don't support clipboard API
                        const textArea = document.createElement('textarea');
                        textArea.value = block.textContent;
                        document.body.appendChild(textArea);
                        textArea.select();
                        document.execCommand('copy');
                        document.body.removeChild(textArea);
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
            <div class="prose prose-lg max-w-none dark:prose-invert" id="documentation-content">
                <?php echo $doc->rendered_content; ?>
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