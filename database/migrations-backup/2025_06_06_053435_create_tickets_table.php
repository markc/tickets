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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('subject');
            $table->longText('content');
            $table->foreignId('creator_id')->constrained('users');
            $table->foreignId('assigned_to_id')->nullable()->constrained('users');
            $table->foreignId('office_id')->constrained();
            $table->foreignId('ticket_status_id')->constrained();
            $table->foreignId('ticket_priority_id')->constrained();
            $table->timestamps();

            $table->index(['ticket_status_id', 'updated_at']);
            $table->index(['assigned_to_id', 'ticket_status_id']);
            $table->index(['office_id', 'ticket_status_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
