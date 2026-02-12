<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'logo',
        'primary_color',
        'secondary_color',
        'branding',
        'features',
    ];

    protected function casts(): array
    {
        return [
            'branding' => 'array',
            'features' => 'array',
        ];
    }

    public function domains(): HasMany
    {
        return $this->hasMany(TenantDomain::class);
    }

    public function tenantUsers(): HasMany
    {
        return $this->hasMany(TenantUser::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_users')
            ->withPivot(['role_id', 'status'])
            ->withTimestamps();
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'games_tenants')
            ->withPivot(['is_visible', 'starts_at', 'ends_at'])
            ->withTimestamps();
    }

    public function gameEntries(): HasMany
    {
        return $this->hasMany(GameEntry::class);
    }

    public function gameRounds(): HasMany
    {
        return $this->hasMany(GameRound::class);
    }

    public function gameWinners(): HasMany
    {
        return $this->hasMany(GameWinner::class);
    }
}
