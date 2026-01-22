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
        Schema::table('bet_matches', function (Blueprint $table) {
            $table->foreignId('winning_option_id')
                ->nullable()
                ->after('is_visible')
                ->constrained('bet_options')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bet_matches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('winning_option_id');
        });
    }
};
