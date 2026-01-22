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
        Schema::create('bet_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')
                ->constrained('bet_matches')
                ->cascadeOnDelete();
            $table->string('label');
            $table->decimal('initial_odds', 6, 2);
            $table->decimal('current_odds', 6, 2);
            $table->unsignedBigInteger('pool_total')->default(0);
            $table->timestamps();

            $table->index('match_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bet_options');
    }
};
