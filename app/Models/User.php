<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Support\Tenancy\TenantContext;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_superadmin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function tenantMemberships(): HasMany
    {
        return $this->hasMany(TenantUser::class);
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_users')
            ->withPivot(['role_id', 'status'])
            ->withTimestamps();
    }

    public function activeMembershipForTenant(?int $tenantId = null): ?TenantUser
    {
        $tenantId ??= app(TenantContext::class)->tenantId();

        if (! $tenantId) {
            return null;
        }

        return $this->tenantMemberships()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with('role.permissions')
            ->first();
    }

    public function hasTenantPermission(string $permission, ?int $tenantId = null): bool
    {
        if ((bool) $this->is_superadmin) {
            return true;
        }

        $membership = $this->activeMembershipForTenant($tenantId);

        return (bool) $membership?->role?->hasPermissionTo($permission);
    }
}
