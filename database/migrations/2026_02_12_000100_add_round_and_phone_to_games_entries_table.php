<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games_entries', function (Blueprint $table) {
            $table->foreignId('game_round_id')
                ->nullable()
                ->after('game_id')
                ->constrained('games_rounds')
                ->nullOnDelete();

            $table->string('participant_phone', 40)
                ->nullable()
                ->after('participant_email');

            $table->index(['tenant_id', 'game_round_id'], 'games_entries_tenant_round_idx');
        });
    }

    public function down(): void
    {
        Schema::table('games_entries', function (Blueprint $table) {
            $table->dropIndex('games_entries_tenant_round_idx');
            $table->dropConstrainedForeignId('game_round_id');
            $table->dropColumn('participant_phone');
        });
    }
};
