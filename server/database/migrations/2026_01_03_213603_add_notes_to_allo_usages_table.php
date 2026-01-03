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
        Schema::table('allo_usages', function (Blueprint $table): void {
            $table->text('user_note')->nullable()->after('points_spent');
            $table->text('admin_note')->nullable()->after('user_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allo_usages', function (Blueprint $table): void {
            $table->dropColumn(['user_note', 'admin_note']);
        });
    }
};
