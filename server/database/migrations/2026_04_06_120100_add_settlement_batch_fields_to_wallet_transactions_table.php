<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->uuid('batch_uuid')->nullable()->after('type');
            $table->string('batch_type')->nullable()->after('batch_uuid');
            $table->index('batch_uuid');
        });

        DB::statement("
            ALTER TABLE wallet_transactions
            MODIFY COLUMN type ENUM(
                'INITIAL',
                'BET_PLACE',
                'BET_UPDATE_DIFF',
                'BET_CANCEL_REFUND',
                'PAYOUT',
                'PAYOUT_UNDO',
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
                'ADJUSTMENT',
                'MATCH_DELETED_REFUND'
            ) NOT NULL
        ");

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropIndex(['batch_uuid']);
            $table->dropColumn(['batch_uuid', 'batch_type']);
        });
    }
};
