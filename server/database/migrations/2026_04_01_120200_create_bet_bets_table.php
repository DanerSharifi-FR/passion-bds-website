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
        Schema::create('bet_bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')
                ->constrained('bet_matches')
                ->cascadeOnDelete();
            $table->foreignId('option_id')
                ->constrained('bet_options')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('stake');
            $table->decimal('odds_locked', 6, 2);
            $table->enum('status', ['ACTIVE', 'CANCELLED', 'SETTLED']);
            $table->dateTime('editable_until');
            $table->timestamps();

            $table->index(['match_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['match_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bet_bets');
    }
};
