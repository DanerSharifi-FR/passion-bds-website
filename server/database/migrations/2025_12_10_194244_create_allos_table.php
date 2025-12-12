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
        Schema::create('allos', function (Blueprint $table): void {
            $table->bigIncrements('id');

            $table->string('title', 255);
            $table->string('slug', 255)->nullable();
            $table->text('description')->nullable();

            $table->integer('points_cost');
            $table->string('status', 20);

            $table->timestamp('window_start_at');
            $table->timestamp('window_end_at');

            $table->integer('slot_duration_minutes');

            $table->foreignId('created_by_id')
                ->constrained('users');

            $table->foreignId('updated_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps(); // created_at, updated_at

            $table->unique('slug');
        });

        // DB-level constraints matching the schema doc
        DB::statement("
            ALTER TABLE allos
            ADD CONSTRAINT chk_allos_points_cost_min0
            CHECK (points_cost >= 0)
        ");

        DB::statement("
            ALTER TABLE allos
            ADD CONSTRAINT chk_allos_window_end_after_start
            CHECK (window_end_at > window_start_at)
        ");

        DB::statement("
            ALTER TABLE allos
            ADD CONSTRAINT chk_allos_slot_duration_positive
            CHECK (slot_duration_minutes > 0)
        ");

        DB::statement("
            ALTER TABLE allos
            ADD CONSTRAINT chk_allos_status_valid
            CHECK (status IN ('DRAFT','OPEN','CLOSED','DISABLED'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allos');
    }
};
