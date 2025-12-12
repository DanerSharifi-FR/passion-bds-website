<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('allo_usages', function (Blueprint $table): void {
            $table->bigIncrements('id');

            $table->foreignId('allo_id')
                ->constrained('allos')
                ->cascadeOnDelete();

            $table->foreignId('allo_slot_id')
                ->constrained('allo_slots')
                ->cascadeOnDelete();

            $table->timestamp('slot_start_at');

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->integer('points_spent');

            $table->string('status', 20);

            $table->foreignId('handled_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('done_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('done_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // One usage per user per slot start
            $table->unique(['user_id', 'slot_start_at'], 'uq_au_user_slotstart');
        });

        // Keep only constraints that don't touch FK columns
        DB::statement("
            ALTER TABLE allo_usages
            ADD CONSTRAINT chk_au_points_spent_min0
            CHECK (points_spent >= 0)
        ");

        DB::statement("
            ALTER TABLE allo_usages
            ADD CONSTRAINT chk_au_status_valid
            CHECK (status IN ('PENDING','ACCEPTED','DONE','CANCELLED'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allo_usages');
    }
};
