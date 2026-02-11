<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameTenant extends Model
{
    protected $table = 'games_tenants';

    protected $fillable = [
        'game_id',
        'tenant_id',
        'is_visible',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
