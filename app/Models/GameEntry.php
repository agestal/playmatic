<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GameEntry extends Model
{
    use BelongsToTenant;

    protected $table = 'games_entries';

    protected $fillable = [
        'tenant_id',
        'game_id',
        'participant_user_id',
        'participant_name',
        'participant_email',
        'status',
        'score',
        'answer_payload',
        'submitted_at',
        'evaluated_at',
    ];

    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'game_id' => 'integer',
            'participant_user_id' => 'integer',
            'score' => 'decimal:2',
            'answer_payload' => 'array',
            'submitted_at' => 'datetime',
            'evaluated_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function participantUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_user_id');
    }

    public function winner(): HasOne
    {
        return $this->hasOne(GameWinner::class);
    }
}
