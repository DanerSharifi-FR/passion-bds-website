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
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id(); // BIGINT PK

            // User who gains/loses points
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Positive or negative number of points
            $table->integer('amount');

            // Short reason label
            $table->string('reason', 255);

            // Optional “link” to another entity (allo, challenge, etc.)
            $table->string('context_type', 50)->nullable();
            $table->unsignedBigInteger('context_id')->nullable();

            // Admin / system user who created the transaction
            $table->foreignId('created_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Only created_at is tracked for this table
            $table->timestamp('created_at')->useCurrent();
        });

        // DB-level rule: a transaction can’t be 0 points
        DB::statement("
            ALTER TABLE point_transactions
            ADD CONSTRAINT chk_pt_amount_not_zero
            CHECK (amount <> 0)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};
