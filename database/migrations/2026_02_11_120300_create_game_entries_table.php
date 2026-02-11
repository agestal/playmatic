<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('participant_name')->nullable();
            $table->string('participant_email')->nullable();
            $table->string('status')->default('submitted');
            $table->decimal('score', 8, 2)->nullable();
            $table->json('answer_payload')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'game_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['game_id', 'participant_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games_entries');
    }
};
