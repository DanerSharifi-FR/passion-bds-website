<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allos', function (Blueprint $table): void {
            $table->integer('security_margin_minutes')->default(0)->after('slot_duration_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('allos', function (Blueprint $table): void {
            $table->dropColumn('security_margin_minutes');
        });
    }
};
