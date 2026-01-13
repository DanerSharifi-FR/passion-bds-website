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
            $table->unsignedInteger('daily_booking_limit')->nullable()->after('security_margin_minutes');
        });

        DB::statement("
            ALTER TABLE allos
            ADD CONSTRAINT chk_allos_daily_booking_limit_positive
            CHECK (daily_booking_limit IS NULL OR daily_booking_limit > 0)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE allos DROP CONSTRAINT IF EXISTS chk_allos_daily_booking_limit_positive');
        DB::statement('ALTER TABLE allos DROP CHECK chk_allos_daily_booking_limit_positive');

        Schema::table('allos', function (Blueprint $table): void {
            $table->dropColumn('daily_booking_limit');
        });
    }
};
