<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE bet_options
            MODIFY label VARCHAR(255) NULL,
            MODIFY initial_odds DECIMAL(6,2) NULL,
            MODIFY current_odds DECIMAL(6,2) NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE bet_options
            MODIFY label VARCHAR(255) NOT NULL,
            MODIFY initial_odds DECIMAL(6,2) NOT NULL,
            MODIFY current_odds DECIMAL(6,2) NOT NULL
        ");
    }
};
