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
        Schema::table('email_templates', function (Blueprint $table) {
            // Drop the existing unique constraint on 'name'
            $table->dropUnique(['name']);

            // Add a unique constraint on 'name' + 'language' combination
            $table->unique(['name', 'language']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            // Drop the compound unique constraint
            $table->dropUnique(['name', 'language']);

            // Restore the original unique constraint on 'name'
            $table->unique('name');
        });
    }
};
