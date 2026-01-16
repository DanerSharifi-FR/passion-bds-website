<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            if (!Schema::hasColumn('audit_logs', 'block_key')) {
                // 191 safe for older MySQL index limits
                $table->string('block_key', 191)->nullable()->index();
            }

            // Optional but useful for the query pattern (action + block_key + created_at)
            //if (!Schema::hasColumn('audit_logs', 'block_key_action_created_idx')) {
                // can't check indexes via hasColumn; just attempt add with a stable name
            //}
        });

        // Add composite index in a separate call so we can name it
        Schema::table('audit_logs', function (Blueprint $table): void {
            // If your DB already has an index with this name, rename it here
            $table->index(['action', 'block_key', 'created_at'], 'audit_logs_action_block_key_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            // Drop composite index first
            $table->dropIndex('audit_logs_action_block_key_created_at_idx');

            if (Schema::hasColumn('audit_logs', 'block_key')) {
                $table->dropColumn('block_key');
            }
        });
    }
};
