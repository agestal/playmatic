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
        Schema::create('games_attendance_guess_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('game_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('winners_count')->default(1);
            $table->boolean('ranking_enabled')->default(false);
            $table->timestamps();
            $table->unique(['tenant_id', 'game_id'], 'games_attendance_guess_settings_tenant_game_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games_attendance_guess_settings');
    }
};
