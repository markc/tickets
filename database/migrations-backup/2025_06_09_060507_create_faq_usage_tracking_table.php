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
        Schema::create('faq_usage_tracking', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faq_id');
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('user_id');
            $table->string('context')->default('reply_insertion'); // reply_insertion, suggestion_view, search_result
            $table->timestamps();

            // Indexes for performance
            $table->index(['faq_id', 'created_at']); // FAQ usage over time
            $table->index(['ticket_id', 'created_at']); // Ticket FAQ usage
            $table->index(['user_id', 'created_at']); // User FAQ usage
            $table->index('context'); // Usage by context
            $table->index('created_at'); // Time-based queries

            // Foreign keys
            $table->foreign('faq_id')->references('id')->on('f_a_q_s')->onDelete('cascade');
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faq_usage_tracking');
    }
};
