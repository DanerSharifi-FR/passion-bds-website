<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute la capacité aux slots d'allos.
     */
    public function up(): void
    {
        Schema::table('allo_slots', function (Blueprint $table): void {
            $table->unsignedInteger('capacity')->default(0)->after('status');
        });

        DB::statement('
            UPDATE allo_slots
            SET capacity = COALESCE((
                SELECT COUNT(*)
                FROM allo_admins
                WHERE allo_admins.allo_id = allo_slots.allo_id
            ), 0)
        ');
    }

    /**
     * Supprime la colonne capacity.
     */
    public function down(): void
    {
        Schema::table('allo_slots', function (Blueprint $table): void {
            $table->dropColumn('capacity');
        });
    }
};
