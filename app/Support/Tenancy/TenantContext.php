<?php

namespace App\Support\Tenancy;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;

class TenantContext
{
    protected ?Tenant $tenant = null;

    protected ?TenantUser $membership = null;

    public function setTenant(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
        $this->membership = null;
    }

    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function tenantId(): ?int
    {
        return $this->tenant?->id;
    }

    public function hasTenant(): bool
    {
        return $this->tenant instanceof Tenant;
    }

    public function setMembership(?TenantUser $membership): void
    {
        $this->membership = $membership;
    }

    public function membership(?User $user = null): ?TenantUser
    {
        $user ??= auth()->user();

        if (! $user || ! $this->tenant) {
            return null;
        }

        if (
            $this->membership
            && $this->membership->tenant_id === $this->tenant->id
            && $this->membership->user_id === $user->id
        ) {
            return $this->membership;
        }

        return $this->membership = TenantUser::query()
            ->where('tenant_id', $this->tenant->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->with('role.permissions')
            ->first();
    }

    public function role(?User $user = null): ?Role
    {
        return $this->membership($user)?->role;
    }
}
