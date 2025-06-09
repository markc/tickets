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
        Schema::create('s_l_a_s', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('office_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_priority_id')->constrained()->onDelete('cascade');
            $table->integer('response_time_minutes'); // Time to first response
            $table->integer('resolution_time_minutes'); // Time to resolution
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['office_id', 'ticket_priority_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('s_l_a_s');
    }
};
