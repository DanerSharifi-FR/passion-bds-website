<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE allos DROP CONSTRAINT chk_allos_slot_duration_positive');

        Schema::table('allos', function (Blueprint $table): void {
            $table->integer('slot_duration_minutes')->nullable()->change();
        });

        DB::statement(<<<SQL
            ALTER TABLE allos
            ADD CONSTRAINT chk_allos_slot_duration_positive
            CHECK (slot_duration_minutes IS NULL OR slot_duration_minutes > 0)
        SQL);
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE allos DROP CONSTRAINT chk_allos_slot_duration_positive');

        Schema::table('allos', function (Blueprint $table): void {
            $table->integer('slot_duration_minutes')->nullable(false)->change();
        });

        DB::statement(<<<SQL
            ALTER TABLE allos
            ADD CONSTRAINT chk_allos_slot_duration_positive
            CHECK (slot_duration_minutes > 0)
        SQL);
    }
};
