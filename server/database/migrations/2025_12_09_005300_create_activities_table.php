<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id(); // BIGINT PK
            $table->timestamps();

            $table->string('title', 255);
            $table->unique('title');
            $table->string('slug', 255)->unique();

            // what points are called for this activity (wins, seconds, kills, etc.)
            $table->string('points_label', 50);

            // INDIVIDUAL or TEAM
            $table->string('mode', 20)->default('INDIVIDUAL');

            $table->boolean('is_active')->default(true);

            // creator (nullable to avoid FK pain if account is deleted later)
            $table->foreignId('created_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->index(['is_active', 'created_at']);
        });

        DB::statement("
            ALTER TABLE activities
            ADD CONSTRAINT chk_activities_mode_valid
            CHECK (mode IN ('INDIVIDUAL','TEAM'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
