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
        Schema::table('tickets', function (Blueprint $table) {
            $table->uuid('merged_into_id')->nullable()->index();
            $table->timestamp('merged_at')->nullable();
            $table->unsignedBigInteger('merged_by_id')->nullable();
            $table->text('merge_reason')->nullable();
            $table->boolean('is_merged')->default(false)->index();

            $table->foreign('merged_into_id')->references('uuid')->on('tickets')->onDelete('set null');
            $table->foreign('merged_by_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['merged_into_id']);
            $table->dropForeign(['merged_by_id']);
            $table->dropColumn([
                'merged_into_id',
                'merged_at',
                'merged_by_id',
                'merge_reason',
                'is_merged',
            ]);
        });
    }
};
