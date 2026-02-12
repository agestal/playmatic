<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameRound extends Model
{
    use BelongsToTenant;

    protected $table = 'games_rounds';

    protected $fillable = [
        'tenant_id',
        'game_id',
        'name',
        'management_mode',
        'starts_at',
        'ends_at',
        'activated_at',
        'deactivated_at',
        'result_value',
        'result_recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'game_id' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'activated_at' => 'datetime',
            'deactivated_at' => 'datetime',
            'result_value' => 'integer',
            'result_recorded_at' => 'datetime',
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

    public function entries(): HasMany
    {
        return $this->hasMany(GameEntry::class, 'game_round_id');
    }

    public function winners(): HasMany
    {
        return $this->hasMany(GameWinner::class, 'game_round_id');
    }

    public function scopeActiveAt(Builder $query, ?CarbonInterface $at = null): Builder
    {
        $at ??= now();

        return $query->where(function (Builder $activeQuery) use ($at): void {
            $activeQuery
                ->where(function (Builder $manualQuery): void {
                    $manualQuery
                        ->where('management_mode', 'manual')
                        ->whereNotNull('activated_at')
                        ->whereNull('deactivated_at');
                })
                ->orWhere(function (Builder $scheduledQuery) use ($at): void {
                    $scheduledQuery
                        ->where('management_mode', 'scheduled')
                        ->whereNull('deactivated_at')
                        ->whereNotNull('starts_at')
                        ->whereNotNull('ends_at')
                        ->where('starts_at', '<=', $at)
                        ->where('ends_at', '>=', $at);
                });
        });
    }

    public function isActiveAt(?CarbonInterface $at = null): bool
    {
        $at ??= now();

        if ($this->deactivated_at !== null) {
            return false;
        }

        if ($this->management_mode === 'manual') {
            return $this->activated_at !== null;
        }

        if ($this->management_mode === 'scheduled') {
            if (! $this->starts_at || ! $this->ends_at) {
                return false;
            }

            return $this->starts_at->lte($at) && $this->ends_at->gte($at);
        }

        return false;
    }
}
