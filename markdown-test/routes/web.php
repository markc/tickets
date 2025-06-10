<?php

use Illuminate\Support\Facades\Route;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-markdown', function () {
    // Read the test markdown file
    $markdownPath = base_path('test-markdown.md');

    if (! file_exists($markdownPath)) {
        return response()->json([
            'error' => 'Markdown file not found',
            'path' => $markdownPath,
        ], 404);
    }

    $markdownContent = file_get_contents($markdownPath);

    // Create environment with GitHub-flavored markdown extensions
    $environment = new Environment([
        'html_input' => 'strip',
        'allow_unsafe_links' => false,
        'max_nesting_level' => PHP_INT_MAX,
    ]);

    // Add core CommonMark functionality
    $environment->addExtension(new CommonMarkCoreExtension);

    // Add GitHub-flavored markdown extensions (includes tables, strikethrough, autolinks, task lists)
    $environment->addExtension(new GithubFlavoredMarkdownExtension);

    $converter = new MarkdownConverter($environment);

    // Convert markdown to HTML
    $convertedHtml = $converter->convert($markdownContent)->getContent();

    // Prepare debug information
    $debugInfo = [
        'markdown_length' => strlen($markdownContent),
        'html_length' => strlen($convertedHtml),
        'first_100_chars_markdown' => substr($markdownContent, 0, 100),
        'first_100_chars_html' => substr($convertedHtml, 0, 100),
        'inline_code_test' => 'Looking for: `composer install`',
        'has_inline_code' => strpos($convertedHtml, '<code>') !== false,
        'has_code_blocks' => strpos($convertedHtml, '<pre>') !== false,
        'raw_backticks_present' => strpos($convertedHtml, '`') !== false,
    ];

    return response()->json([
        'status' => 'success',
        'html' => $convertedHtml,
        'debug' => $debugInfo,
    ]);
});

Route::get('/load-markdown', function () {
    return view('welcome');
});
