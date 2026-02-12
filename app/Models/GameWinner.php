<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameWinner extends Model
{
    use BelongsToTenant;

    protected $table = 'games_winners';

    protected $fillable = [
        'tenant_id',
        'game_id',
        'game_round_id',
        'game_entry_id',
        'participant_user_id',
        'participant_name',
        'participant_email',
        'position',
        'prize_name',
        'prize_value',
        'winner_payload',
        'notes',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'game_id' => 'integer',
            'game_round_id' => 'integer',
            'game_entry_id' => 'integer',
            'participant_user_id' => 'integer',
            'position' => 'integer',
            'winner_payload' => 'array',
            'decided_at' => 'datetime',
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

    public function gameRound(): BelongsTo
    {
        return $this->belongsTo(GameRound::class);
    }

    public function gameEntry(): BelongsTo
    {
        return $this->belongsTo(GameEntry::class);
    }

    public function participantUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_user_id');
    }
}
