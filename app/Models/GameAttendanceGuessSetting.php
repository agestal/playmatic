<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameAttendanceGuessSetting extends Model
{
    protected $table = 'games_attendance_guess_settings';

    protected $fillable = [
        'tenant_id',
        'game_id',
        'winners_count',
        'ranking_enabled',
    ];

    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'game_id' => 'integer',
            'winners_count' => 'integer',
            'ranking_enabled' => 'boolean',
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
}
