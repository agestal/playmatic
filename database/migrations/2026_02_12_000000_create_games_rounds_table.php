<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->string('name', 160);
            $table->string('management_mode', 20)->default('manual');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->unsignedBigInteger('result_value')->nullable();
            $table->timestamp('result_recorded_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'game_id']);
            $table->index(['tenant_id', 'game_id', 'management_mode'], 'games_rounds_tenant_game_mode_idx');
            $table->index(['tenant_id', 'game_id', 'starts_at', 'ends_at'], 'games_rounds_tenant_game_window_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games_rounds');
    }
};
