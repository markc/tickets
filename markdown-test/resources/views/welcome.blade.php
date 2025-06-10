<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GitHub-Style Markdown Test</title>
    
    <!-- Tailwind CSS for base styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Highlight.js for syntax highlighting -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css" media="(prefers-color-scheme: dark)">
    
    <style>
        /* GitHub-accurate CSS variables and styling */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
            font-size: 16px;
            line-height: 1.5;
            color: #24292f;
            background-color: #ffffff;
            margin: 0;
            padding: 40px;
        }
        
        .markdown-body {
            box-sizing: border-box;
            min-width: 200px;
            max-width: 980px;
            margin: 0 auto;
            padding: 45px;
            background-color: #ffffff;
            border: 1px solid #d1d9e0;
            border-radius: 6px;
        }
        
        /* Headers with exact GitHub styling */
        .markdown-body h1 {
            font-size: 2em;
            font-weight: 600;
            line-height: 1.25;
            margin: 0;
            padding-bottom: 0.3em;
            border-bottom: 1px solid #d1d9e0;
            margin-bottom: 16px;
            margin-top: 24px;
            color: #1f2328;
        }
        
        .markdown-body h1:first-child {
            margin-top: 0;
        }
        
        .markdown-body h2 {
            font-size: 1.5em;
            font-weight: 600;
            line-height: 1.25;
            margin: 0;
            padding-bottom: 0.3em;
            border-bottom: 1px solid #d1d9e0;
            margin-bottom: 16px;
            margin-top: 24px;
            color: #1f2328;
        }
        
        .markdown-body h3 {
            font-size: 1.25em;
            font-weight: 600;
            line-height: 1.25;
            margin: 0;
            margin-bottom: 16px;
            margin-top: 24px;
            color: #1f2328;
        }
        
        .markdown-body h4, .markdown-body h5, .markdown-body h6 {
            font-size: 1em;
            font-weight: 600;
            line-height: 1.25;
            margin: 0;
            margin-bottom: 16px;
            margin-top: 24px;
            color: #1f2328;
        }
        
        /* Paragraphs */
        .markdown-body p {
            margin-top: 0;
            margin-bottom: 16px;
        }
        
        /* GitHub-exact inline code styling */
        .markdown-body code {
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace;
            font-size: 85%;
            padding: 0.2em 0.4em;
            margin: 0;
            background-color: rgba(175, 184, 193, 0.2);
            border-radius: 6px;
            color: #1f2328;
            border: none;
        }
        
        /* GitHub-exact code block styling */
        .markdown-body pre {
            margin-top: 0;
            margin-bottom: 16px;
            padding: 16px;
            overflow: auto;
            font-size: 85%;
            line-height: 1.45;
            background-color: #f6f8fa;
            border-radius: 6px;
            border: 1px solid #d1d9e0;
            position: relative;
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace;
        }
        
        .markdown-body pre code {
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace;
            padding: 0;
            margin: 0;
            background-color: transparent;
            border: 0;
            font-size: 100%;
            color: #24292f;
            word-break: normal;
            white-space: pre;
            border-radius: 0;
        }
        
        /* Lists */
        .markdown-body ul, .markdown-body ol {
            margin-top: 0;
            margin-bottom: 16px;
            padding-left: 2em;
        }
        
        .markdown-body ul {
            list-style-type: disc;
        }
        
        .markdown-body ol {
            list-style-type: decimal;
        }
        
        .markdown-body li {
            margin-bottom: 0.25em;
            word-wrap: break-all;
        }
        
        .markdown-body li > p {
            margin-top: 16px;
            margin-bottom: 16px;
        }
        
        .markdown-body li + li {
            margin-top: 0.25em;
        }
        
        /* Task lists */
        .markdown-body .task-list-item {
            list-style-type: none;
        }
        
        .markdown-body .task-list-item input {
            margin: 0 0.2em 0.25em -1.6em;
            vertical-align: middle;
        }
        
        /* Tables */
        .markdown-body table {
            border-spacing: 0;
            border-collapse: collapse;
            margin-top: 0;
            margin-bottom: 16px;
            display: block;
            width: max-content;
            max-width: 100%;
            overflow: auto;
        }
        
        .markdown-body th, .markdown-body td {
            padding: 6px 13px;
            border: 1px solid #d1d9e0;
        }
        
        .markdown-body th {
            font-weight: 600;
            background-color: #f6f8fa;
        }
        
        /* Blockquotes */
        .markdown-body blockquote {
            margin: 0;
            padding: 0 1em;
            color: #656d76;
            border-left: 0.25em solid #d1d9e0;
            margin-bottom: 16px;
        }
        
        /* Links */
        .markdown-body a {
            color: #0969da;
            text-decoration: none;
        }
        
        .markdown-body a:hover {
            text-decoration: underline;
        }
        
        /* Horizontal rules */
        .markdown-body hr {
            height: 0.25em;
            padding: 0;
            margin: 24px 0;
            background-color: #d1d9e0;
            border: 0;
        }
        
        /* Emphasis and strong */
        .markdown-body strong {
            font-weight: 600;
        }
        
        .markdown-body em {
            font-style: italic;
        }
        
        /* Strikethrough */
        .markdown-body del {
            text-decoration: line-through;
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            body {
                color: #c9d1d9;
                background-color: #0d1117;
            }
            
            .markdown-body {
                background-color: #0d1117;
                border-color: #30363d;
                color: #c9d1d9;
            }
            
            .markdown-body h1, .markdown-body h2 {
                border-bottom-color: #30363d;
                color: #e6edf3;
            }
            
            .markdown-body h3, .markdown-body h4, .markdown-body h5, .markdown-body h6 {
                color: #e6edf3;
            }
            
            .markdown-body code {
                background-color: rgba(110, 118, 129, 0.4);
                color: #f0f6fc;
            }
            
            .markdown-body pre {
                background-color: #161b22;
                border-color: #30363d;
            }
            
            .markdown-body pre code {
                color: #c9d1d9;
            }
            
            .markdown-body th {
                background-color: #161b22;
                border-color: #30363d;
            }
            
            .markdown-body td {
                border-color: #30363d;
            }
            
            .markdown-body blockquote {
                color: #848d97;
                border-left-color: #30363d;
            }
            
            .markdown-body a {
                color: #58a6ff;
            }
            
            .markdown-body hr {
                background-color: #30363d;
            }
        }
    </style>
</head>
<body>
    <div class="markdown-body">
        <h1>Laravel Markdown Test</h1>
        <p>This page will test GitHub-flavored markdown rendering using League CommonMark.</p>
        
        <h2>Test Status</h2>
        <ul>
            <li>✅ Fresh Laravel 12 project created</li>
            <li>✅ League CommonMark with GFM extension installed</li>
            <li>✅ Test markdown file created</li>
            <li>🔄 Testing markdown conversion...</li>
        </ul>
        
        <h2>Expected Results</h2>
        <p>We should see proper rendering of:</p>
        <ul>
            <li><code>Inline code</code> with background styling</li>
            <li>Code blocks with syntax highlighting</li>
            <li>All GitHub markdown features</li>
        </ul>
        
        <h2>Converted Markdown Content</h2>
        <div id="markdown-content">
            <!-- Markdown content will be inserted here -->
        </div>
        
        <hr>
        
        <h2>Debug Information</h2>
        <div id="debug-info" style="background: #f6f8fa; padding: 16px; border-radius: 6px; margin: 16px 0;">
            <!-- Debug info will be inserted here -->
        </div>
    </div>
    
    <!-- Highlight.js for syntax highlighting -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/bash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/json.min.js"></script>
    
    <script>
        // Initialize syntax highlighting when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Configure highlight.js
            hljs.configure({
                ignoreUnescapedHTML: true,
                languages: ['php', 'bash', 'javascript', 'json']
            });
            
            // Highlight all code blocks
            hljs.highlightAll();
            
            console.log('Syntax highlighting initialized');
        });
        
        // Function to load and convert markdown (will be called from Laravel route)
        function loadMarkdownContent(htmlContent, debugInfo = '') {
            document.getElementById('markdown-content').innerHTML = htmlContent;
            document.getElementById('debug-info').innerHTML = debugInfo;
            
            // Re-run syntax highlighting on new content
            document.querySelectorAll('#markdown-content pre code').forEach((block) => {
                hljs.highlightElement(block);
            });
            
            console.log('Markdown content loaded and highlighted');
        }
        
        // Auto-load markdown content when page loads
        window.addEventListener('load', function() {
            console.log('Loading markdown content...');
            
            fetch('/test-markdown')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Load the converted HTML
                        loadMarkdownContent(data.html, formatDebugInfo(data.debug));
                        console.log('Markdown conversion successful');
                    } else {
                        document.getElementById('markdown-content').innerHTML = '<p style="color: red;">Error: ' + (data.error || 'Unknown error') + '</p>';
                        document.getElementById('debug-info').innerHTML = '<p>Failed to load markdown</p>';
                    }
                })
                .catch(error => {
                    console.error('Error loading markdown:', error);
                    document.getElementById('markdown-content').innerHTML = '<p style="color: red;">Error loading markdown: ' + error.message + '</p>';
                    document.getElementById('debug-info').innerHTML = '<p>Fetch error: ' + error.message + '</p>';
                });
        });
        
        // Format debug information for display
        function formatDebugInfo(debug) {
            let html = '<h3>Debug Information</h3>';
            html += '<ul>';
            for (const [key, value] of Object.entries(debug)) {
                html += `<li><strong>${key}:</strong> ${value}</li>`;
            }
            html += '</ul>';
            return html;
        }
    </script>
</body>
</html>