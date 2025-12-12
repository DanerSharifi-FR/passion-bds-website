<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // PK
            $table->bigIncrements('id');

            // Business columns
            $table->string('university_email', 255)->unique();
            $table->string('display_name', 255)->nullable();
            $table->string('avatar_url', 500)->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamp('last_login_at')->nullable();

            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
