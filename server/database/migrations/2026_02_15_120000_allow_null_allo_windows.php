<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('allos', function (Blueprint $table): void {
            $table->timestamp('window_start_at')->nullable()->change();
            $table->timestamp('window_end_at')->nullable()->change();
        });

        DB::statement('ALTER TABLE allos DROP CONSTRAINT chk_allos_window_end_after_start');
        DB::statement("
            ALTER TABLE allos
            ADD CONSTRAINT chk_allos_window_end_after_start
            CHECK (
                (window_start_at IS NULL AND window_end_at IS NULL)
                OR (window_end_at > window_start_at)
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE allos DROP CONSTRAINT chk_allos_window_end_after_start');

        Schema::table('allos', function (Blueprint $table): void {
            $table->timestamp('window_start_at')->nullable(false)->change();
            $table->timestamp('window_end_at')->nullable(false)->change();
        });

        DB::statement("
            ALTER TABLE allos
            ADD CONSTRAINT chk_allos_window_end_after_start
            CHECK (window_end_at > window_start_at)
        ");
    }
};
