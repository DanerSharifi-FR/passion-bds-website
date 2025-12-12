<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pole_id')->constrained('poles');

            $table->string('full_name', 255);
            $table->string('nickname', 100)->nullable();
            $table->text('bio')->nullable();

            $table->string('photo_url', 500)->nullable();
            $table->string('instagram_url', 255)->nullable();

            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_visible')->default(true);

            $table->timestamps();

            $table->index(['pole_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
