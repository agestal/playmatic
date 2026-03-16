<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games_attendance_guess_settings', function (Blueprint $table) {
            $table->unsignedInteger('max_capacity')
                ->nullable()
                ->after('ranking_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('games_attendance_guess_settings', function (Blueprint $table) {
            $table->dropColumn('max_capacity');
        });
    }
};
