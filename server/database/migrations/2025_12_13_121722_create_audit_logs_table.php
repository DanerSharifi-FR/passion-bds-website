<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('actor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete(); // keep logs, allow user deletion, and supports anon logs

            $table->string('action', 100);
            $table->string('entity_type', 100);
            $table->unsignedBigInteger('entity_id')->nullable();

            $table->string('description', 255)->nullable();
            $table->text('metadata_json')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            // schema says TIMESTAMP created_at only (no updated_at)
            $table->timestamp('created_at')->useCurrent();

            // indexes (you will query these a lot in admin)
            $table->index(['actor_id', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('action');
        });

        // Optional DB-level checks (safe-guarded for MySQL)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE audit_logs ADD CONSTRAINT chk_audit_logs_action_not_empty CHECK (action <> '')");
            DB::statement("ALTER TABLE audit_logs ADD CONSTRAINT chk_audit_logs_entity_type_not_empty CHECK (entity_type <> '')");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // If the constraints exist, drop them before dropping the table
            try {
                DB::statement("ALTER TABLE audit_logs DROP CHECK chk_audit_logs_action_not_empty");
            } catch (\Throwable) {}
            try {
                DB::statement("ALTER TABLE audit_logs DROP CHECK chk_audit_logs_entity_type_not_empty");
            } catch (\Throwable) {}
        }

        Schema::dropIfExists('audit_logs');
    }
};
