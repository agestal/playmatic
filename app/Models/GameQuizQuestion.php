<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GameQuizQuestion extends Model
{
    use BelongsToTenant;

    protected $table = 'games_quiz_questions';

    protected $fillable = [
        'tenant_id',
        'question',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(GameQuizAnswer::class, 'question_id');
    }

    public function correctAnswer(): HasOne
    {
        return $this->hasOne(GameQuizAnswer::class, 'question_id')->where('is_correct', true);
    }
}
