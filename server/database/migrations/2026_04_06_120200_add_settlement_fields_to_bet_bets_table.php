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
        Schema::table('bet_bets', function (Blueprint $table) {
            $table->enum('result', ['WON', 'LOST', 'REFUNDED'])->nullable()->after('status');
            $table->uuid('settled_batch_uuid')->nullable()->after('result');

            $table->index(['match_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('settled_batch_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bet_bets', function (Blueprint $table) {
            $table->dropIndex(['match_id', 'status']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['settled_batch_uuid']);
            $table->dropColumn(['result', 'settled_batch_uuid']);
        });
    }
};
