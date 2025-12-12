<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_participants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('activity_id')
                ->constrained('activities')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Who added them (gamemaster/super admin)
            $table->foreignId('created_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('created_at')->useCurrent();

            $table->unique(['activity_id', 'user_id'], 'uq_activity_participants_activity_user');

            // Helpful for leaderboard joins
            $table->index(['activity_id', 'user_id'], 'activity_participants_activity_user_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_participants');
    }
};
