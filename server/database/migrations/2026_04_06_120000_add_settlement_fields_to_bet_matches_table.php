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
        if (Schema::hasColumn('bet_matches', 'winning_option_id')) {
            Schema::table('bet_matches', function (Blueprint $table) {
                $table->dropConstrainedForeignId('winning_option_id');
            });
        }

        Schema::table('bet_matches', function (Blueprint $table) {
            $table->integer('score_a')->nullable()->after('match_end_at');
            $table->integer('score_b')->nullable()->after('score_a');
            $table->boolean('score_is_auto')->default(false)->after('score_b');
            $table->foreignId('winner_option_id')
                ->nullable()
                ->after('is_visible')
                ->constrained('bet_options')
                ->nullOnDelete();
            $table->dateTime('settled_at')->nullable()->after('winner_option_id');
            $table->foreignId('settled_by')
                ->nullable()
                ->after('settled_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->unsignedInteger('settlement_version')->default(0)->after('settled_by');

            $table->index('winner_option_id');
            $table->index('settled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bet_matches', function (Blueprint $table) {
            $table->dropIndex(['winner_option_id']);
            $table->dropIndex(['settled_at']);
            $table->dropConstrainedForeignId('winner_option_id');
            $table->dropConstrainedForeignId('settled_by');
            $table->dropColumn([
                'score_a',
                'score_b',
                'score_is_auto',
                'settled_at',
                'settlement_version',
            ]);
        });

        Schema::table('bet_matches', function (Blueprint $table) {
            $table->foreignId('winning_option_id')
                ->nullable()
                ->constrained('bet_options')
                ->nullOnDelete();
        });
    }
};
