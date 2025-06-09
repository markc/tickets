<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Template identifier (e.g., 'ticket_created', 'ticket_reply')
            $table->string('subject'); // Email subject line with variable support
            $table->longText('content'); // Email body content with variable support
            $table->string('type')->default('markdown'); // Template type: markdown, html, plain
            $table->string('category')->default('general'); // Category for organization
            $table->boolean('is_active')->default(true); // Enable/disable template
            $table->boolean('is_default')->default(false); // System default template
            $table->json('variables')->nullable(); // Available variables for this template
            $table->string('language')->default('en'); // Template language
            $table->text('description')->nullable(); // Description of when template is used
            $table->unsignedBigInteger('created_by_id')->nullable(); // User who created template
            $table->unsignedBigInteger('updated_by_id')->nullable(); // User who last updated
            $table->timestamps();

            // Indexes for performance
            $table->index(['name', 'language']); // Lookup by name and language
            $table->index(['category', 'is_active']); // Filter by category and status
            $table->index('is_default'); // Find default templates

            // Foreign keys
            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
