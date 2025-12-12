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
        Schema::create('login_codes', function (Blueprint $table) {
            $table->id(); // BIGINT PK

            // Relation to users table
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Hashed code sent by email
            $table->string('code_hash', 255);

            // Expiration and usage
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();

            // Attempts and context
            $table->unsignedInteger('attempt_count')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            // Only created_at is tracked for this table
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });

        // DB-level constraints (MySQL 8+)
        DB::statement("
            ALTER TABLE login_codes
            ADD CONSTRAINT chk_login_codes_attempt_min0
            CHECK (attempt_count >= 0)
        ");

        DB::statement("
            ALTER TABLE login_codes
            ADD CONSTRAINT chk_login_codes_expiry_after
            CHECK (expires_at > created_at)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_codes');
    }
};
