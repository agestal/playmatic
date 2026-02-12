<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'game_type',
        'description',
        'is_active',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'config' => 'array',
        ];
    }

    public function tenantLinks(): HasMany
    {
        return $this->hasMany(GameTenant::class);
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'games_tenants')
            ->withPivot(['is_visible', 'starts_at', 'ends_at'])
            ->withTimestamps();
    }

    public function rules(): HasMany
    {
        return $this->hasMany(GameRule::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(GameEntry::class);
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(GameRound::class);
    }

    public function winners(): HasMany
    {
        return $this->hasMany(GameWinner::class);
    }
}
