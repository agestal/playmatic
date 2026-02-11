<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games_winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_entry_id')->nullable()->constrained('games_entries')->cascadeOnDelete();
            $table->foreignId('participant_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('participant_name')->nullable();
            $table->string('participant_email')->nullable();
            $table->unsignedSmallInteger('position')->default(1);
            $table->string('prize_name')->nullable();
            $table->string('prize_value')->nullable();
            $table->json('winner_payload')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'game_id', 'position']);
            $table->index(['tenant_id', 'participant_user_id']);
            $table->unique(['tenant_id', 'game_id', 'game_entry_id'], 'games_winners_tenant_game_entry_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games_winners');
    }
};
