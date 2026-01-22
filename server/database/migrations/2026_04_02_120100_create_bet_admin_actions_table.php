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
        Schema::create('bet_admin_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('bet_id')
                ->nullable()
                ->constrained('bet_bets')
                ->nullOnDelete();
            $table->foreignId('match_id')
                ->nullable()
                ->constrained('bet_matches')
                ->nullOnDelete();
            $table->string('action');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['admin_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bet_admin_actions');
    }
};
