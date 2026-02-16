<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games_quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')
                ->constrained('games_quiz_questions')
                ->cascadeOnDelete();
            $table->text('answer');
            $table->boolean('is_correct')->default(false);
            $table->unsignedBigInteger('correct_question_id')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'question_id']);
            $table->index(['tenant_id', 'is_correct']);
            $table->unique('correct_question_id', 'games_quiz_answers_one_correct_per_question_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games_quiz_answers');
    }
};
