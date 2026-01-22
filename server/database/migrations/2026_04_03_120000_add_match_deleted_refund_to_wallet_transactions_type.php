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
            ALTER TABLE wallet_transactions
            MODIFY COLUMN type ENUM(
                'INITIAL',
                'BET_PLACE',
                'BET_UPDATE_DIFF',
                'BET_CANCEL_REFUND',
                'PAYOUT',
                'ADJUSTMENT',
                'MATCH_DELETED_REFUND'
            ) NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE wallet_transactions
            MODIFY COLUMN type ENUM(
                'INITIAL',
                'BET_PLACE',
                'BET_UPDATE_DIFF',
                'BET_CANCEL_REFUND',
                'PAYOUT',
                'ADJUSTMENT'
            ) NOT NULL
        ");
    }
};
