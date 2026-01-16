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
        Schema::table('allos', function (Blueprint $table): void {
            $table->integer('slot_capacity')->nullable()->after('daily_booking_limit');
        });

        DB::statement(<<<'SQL'
            ALTER TABLE allos
            ADD CONSTRAINT chk_allos_slot_capacity_positive
            CHECK (slot_capacity IS NULL OR slot_capacity > 0)
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE allos DROP CONSTRAINT IF EXISTS chk_allos_slot_capacity_positive');
        DB::statement('ALTER TABLE allos DROP CHECK chk_allos_slot_capacity_positive');

        Schema::table('allos', function (Blueprint $table): void {
            $table->dropColumn('slot_capacity');
        });
    }
};
