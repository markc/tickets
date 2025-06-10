<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="markdownEditor({
        state: $wire.$entangle('{{ $getStatePath() }}'),
        showPreview: {{ $getShowPreview() ? 'true' : 'false' }}
    })" class="markdown-editor">
        <!-- Toolbar -->
        <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-2 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <button type="button" @click="insertText('**', '**')" 
                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                    <strong>B</strong>
                </button>
                <button type="button" @click="insertText('*', '*')" 
                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                    <em>I</em>
                </button>
                <button type="button" @click="insertText('`', '`')" 
                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded font-mono">
                    &lt;/&gt;
                </button>
                <div class="h-4 w-px bg-gray-300 dark:bg-gray-600"></div>
                <button type="button" @click="insertText('## ', '')" 
                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                    H2
                </button>
                <button type="button" @click="insertText('### ', '')" 
                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                    H3
                </button>
                <button type="button" @click="insertText('- ', '')" 
                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                    List
                </button>
                <button type="button" @click="insertText('[', '](url)')" 
                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                    Link
                </button>
            </div>
            
            <div class="flex items-center space-x-2">
                <button type="button" @click="showPreview = !showPreview" 
                        class="inline-flex items-center px-3 py-1 text-xs font-medium rounded border"
                        :class="showPreview ? 'bg-blue-100 text-blue-700 border-blue-300 dark:bg-blue-900 dark:text-blue-300 dark:border-blue-700' : 'bg-white text-gray-700 border-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600'">
                    <x-heroicon-o-eye class="w-4 h-4 mr-1"/>
                    Preview
                </button>
            </div>
        </div>

        <!-- Editor Content -->
        <div class="flex" :class="showPreview ? 'divide-x divide-gray-200 dark:divide-gray-700' : ''">
            <!-- Editor Pane -->
            <div :class="showPreview ? 'w-1/2' : 'w-full'">
                <textarea 
                    x-ref="editor"
                    x-model="state"
                    {{ $attributes->merge($getExtraAttributes())->class([
                        'block w-full border-0 focus:ring-0 font-mono text-sm resize-none',
                        'text-gray-900 dark:text-white bg-white dark:bg-gray-900'
                    ]) }}
                    rows="25"
                    placeholder="Enter your markdown content here..."
                    @keydown.tab.prevent="insertTab($event)"
                ></textarea>
            </div>

            <!-- Preview Pane -->
            <div x-show="showPreview" x-transition class="w-1/2 p-4 bg-gray-50 dark:bg-gray-800 overflow-y-auto max-h-[600px]">
                <div class="prose prose-sm dark:prose-invert max-w-none" x-html="renderMarkdown(state)">
                </div>
            </div>
        </div>
    </div>

    <script>
        function markdownEditor(config) {
            return {
                state: config.state,
                showPreview: config.showPreview,

                insertText(before, after) {
                    const textarea = this.$refs.editor;
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    const selectedText = textarea.value.substring(start, end);
                    
                    const replacement = before + selectedText + after;
                    
                    textarea.setRangeText(replacement, start, end, 'end');
                    this.state = textarea.value;
                    
                    // Focus back on textarea
                    textarea.focus();
                    
                    // Set cursor position
                    if (selectedText === '') {
                        textarea.setSelectionRange(start + before.length, start + before.length);
                    }
                },

                insertTab(event) {
                    const textarea = event.target;
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    
                    textarea.setRangeText('    ', start, end, 'end');
                    this.state = textarea.value;
                },

                renderMarkdown(content) {
                    if (!content) return '';
                    
                    // Basic markdown rendering for preview
                    let html = content;
                    
                    // Headers
                    html = html.replace(/^### (.*$)/gim, '<h3>$1</h3>');
                    html = html.replace(/^## (.*$)/gim, '<h2>$1</h2>');
                    html = html.replace(/^# (.*$)/gim, '<h1>$1</h1>');
                    
                    // Bold
                    html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                    
                    // Italic
                    html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
                    
                    // Code
                    html = html.replace(/`(.*?)`/g, '<code>$1</code>');
                    
                    // Links
                    html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-blue-600 hover:text-blue-800">$1</a>');
                    
                    // Line breaks
                    html = html.replace(/\n/g, '<br>');
                    
                    // Lists
                    html = html.replace(/^- (.*$)/gim, '<li>$1</li>');
                    html = html.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');
                    
                    // Code blocks
                    html = html.replace(/```([\s\S]*?)```/g, '<pre class="bg-gray-100 dark:bg-gray-800 p-2 rounded"><code>$1</code></pre>');
                    
                    return html;
                }
            }
        }
    </script>
</x-dynamic-component>