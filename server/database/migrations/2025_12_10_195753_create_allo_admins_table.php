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
        Schema::create('allo_admins', function (Blueprint $table): void {
            $table->bigIncrements('id');

            $table->foreignId('allo_id')
                ->constrained('allos')
                ->cascadeOnDelete();

            $table->foreignId('admin_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamp('created_at')->useCurrent();

            // Each (allo, admin) pair must be unique
            $table->unique(['allo_id', 'admin_id'], 'uq_allo_admins_allo_admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allo_admins');
    }
};
