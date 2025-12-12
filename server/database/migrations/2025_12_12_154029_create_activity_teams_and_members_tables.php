<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_teams', function (Blueprint $table) {
            $table->id();

            $table->foreignId('activity_id')
                ->constrained('activities')
                ->cascadeOnDelete();

            $table->string('title', 150);

            $table->foreignId('created_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Un nom d’équipe unique par activité
            $table->unique(['activity_id', 'title'], 'uq_activity_teams_activity_title');
        });

        Schema::create('activity_team_members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('team_id')
                ->constrained('activity_teams')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('created_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('created_at')->useCurrent();

            // Un même user ne peut pas être ajouté 2x à la même équipe
            $table->unique(['team_id', 'user_id'], 'uq_activity_team_members_team_user');

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_team_members');
        Schema::dropIfExists('activity_teams');
    }
};
