<?php

namespace App\Support\Authorization;

use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;

class TenantPermissionService
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    public function can(string $permission, ?User $user = null): bool
    {
        $user ??= auth()->user();

        if (! $user) {
            return false;
        }

        if ((bool) $user->is_superadmin) {
            return true;
        }

        $role = $this->tenantContext->role($user);

        if (! $role) {
            return false;
        }

        return $role->hasPermissionTo($permission);
    }

    public function canEntity(string $resource, string $action, ?User $user = null): bool
    {
        return $this->can($resource.'.'.$action.'.entity', $user);
    }

    public function canContent(string $resource, string $action, ?User $user = null): bool
    {
        return $this->can($resource.'.'.$action.'.content', $user);
    }

    public function applyTenantScope(Builder $query, string $tenantColumn = 'tenant_id'): Builder
    {
        $tenantId = $this->tenantContext->tenantId();

        if ($tenantId) {
            $query->where($tenantColumn, $tenantId);
        }

        return $query;
    }
}
