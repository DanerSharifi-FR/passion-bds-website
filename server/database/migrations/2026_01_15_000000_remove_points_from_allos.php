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
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE allos DROP CHECK chk_allos_points_cost_min0');
            DB::statement('ALTER TABLE allo_usages DROP CHECK chk_au_points_spent_min0');
        } else {
            DB::statement('ALTER TABLE allos DROP CONSTRAINT IF EXISTS chk_allos_points_cost_min0');
            DB::statement('ALTER TABLE allo_usages DROP CONSTRAINT IF EXISTS chk_au_points_spent_min0');
        }

        Schema::table('allos', function (Blueprint $table): void {
            $table->dropColumn('points_cost');
        });

        Schema::table('allo_usages', function (Blueprint $table): void {
            $table->dropColumn('points_spent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allos', function (Blueprint $table): void {
            $table->integer('points_cost')->default(0)->after('description');
        });

        Schema::table('allo_usages', function (Blueprint $table): void {
            $table->integer('points_spent')->default(0)->after('user_id');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE allos
                ADD CONSTRAINT chk_allos_points_cost_min0
                CHECK (points_cost >= 0)
            ");
            DB::statement("
                ALTER TABLE allo_usages
                ADD CONSTRAINT chk_au_points_spent_min0
                CHECK (points_spent >= 0)
            ");
        } else {
            DB::statement("
                ALTER TABLE allos
                ADD CONSTRAINT chk_allos_points_cost_min0
                CHECK (points_cost >= 0)
            ");
            DB::statement("
                ALTER TABLE allo_usages
                ADD CONSTRAINT chk_au_points_spent_min0
                CHECK (points_spent >= 0)
            ");
        }
    }
};
