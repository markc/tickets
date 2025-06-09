<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // This migration represents the consolidated schema dump
        // The actual schema is loaded from database/schema/sqlite-schema.sql
        // when running migrate:fresh or on a fresh database

        // For existing databases, this migration will be marked as run
        // without executing anything, since the schema already exists
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot rollback a schema dump - would require dropping all tables
        throw new Exception('Cannot rollback initial schema migration');
    }
};
