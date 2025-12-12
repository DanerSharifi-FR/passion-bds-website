<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_admins', function (Blueprint $table) {
            $table->id();

            $table->foreignId('activity_id')
                ->constrained('activities')
                ->cascadeOnDelete();

            // Invited gamemaster user id
            $table->foreignId('admin_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamp('created_at')->useCurrent();

            $table->unique(['activity_id', 'admin_id'], 'uq_activity_admins_activity_admin');

            // Useful to list "activities I can manage"
            $table->index('admin_id', 'activity_admins_admin_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_admins');
    }
};
