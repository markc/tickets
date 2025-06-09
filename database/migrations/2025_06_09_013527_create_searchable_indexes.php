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
        Schema::create('searchable_indexes', function (Blueprint $table) {
            $table->id();
            $table->morphs('searchable');
            $table->text('content');
            $table->string('type');
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Note: SQLite doesn't support fulltext indexes,
            // but Laravel Scout will still work with LIKE queries
            $table->index(['type']);
            // morphs() already creates the composite index
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('searchable_indexes');
    }
};
