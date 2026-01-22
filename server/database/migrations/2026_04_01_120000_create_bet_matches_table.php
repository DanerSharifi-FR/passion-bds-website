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
        Schema::create('bet_matches', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->dateTime('bet_open_at');
            $table->dateTime('match_start_at');
            $table->dateTime('match_end_at');
            $table->enum('status', ['DRAFT', 'OPEN', 'LOCKED', 'FINISHED', 'CANCELLED']);
            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamps();

            $table->index(['status', 'bet_open_at']);
            $table->index('match_start_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bet_matches');
    }
};
