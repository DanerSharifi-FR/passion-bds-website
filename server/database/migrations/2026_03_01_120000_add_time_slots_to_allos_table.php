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
        Schema::table('allos', function (Blueprint $table): void {
            $table->json('time_slots')->nullable()->after('slot_duration_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allos', function (Blueprint $table): void {
            $table->dropColumn('time_slots');
        });
    }
};
