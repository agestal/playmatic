<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameQuizAnswer extends Model
{
    use BelongsToTenant;

    protected $table = 'games_quiz_answers';

    protected $fillable = [
        'tenant_id',
        'question_id',
        'answer',
        'is_correct',
        'correct_question_id',
    ];

    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'question_id' => 'integer',
            'is_correct' => 'boolean',
            'correct_question_id' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(GameQuizQuestion::class, 'question_id');
    }
}
