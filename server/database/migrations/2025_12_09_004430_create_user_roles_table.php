<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot table between users and roles.
     * Matches schema.md: PK(user_id, role_id).
     */
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table): void {
            // FK columns
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');

            // Composite primary key
            $table->primary(['user_id', 'role_id'], 'pk_user_roles');

            // Foreign keys
            $table->foreign('user_id', 'fk_user_roles_user_id_users')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('role_id', 'fk_user_roles_role_id_roles')
                ->references('id')
                ->on('roles')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
