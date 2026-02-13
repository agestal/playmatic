<?php

namespace App\Support\Tenancy;

use App\Models\Game;
use App\Models\GameTenant;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\TenantUser;
use App\Models\User;
use App\Support\Authorization\PermissionCatalog;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class TenantProvisioningService
{
    /**
     * @return Collection<string, Role>
     */
    public function ensureDefaultRoles(Tenant $tenant): Collection
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionCatalog::names() as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $roles = collect(['tenant_admin', 'tenant_manager', 'tenant_viewer'])
            ->mapWithKeys(function (string $roleName) use ($tenant): array {
                $role = Role::query()->firstOrCreate([
                    'tenant_id' => $tenant->id,
                    'name' => $roleName,
                    'guard_name' => 'web',
                ]);

                if ($role->wasRecentlyCreated) {
                    $role->syncPermissions(PermissionCatalog::defaultsForRole($roleName));
                }

                return [$roleName => $role];
            });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $roles;
    }

    public function assignOwner(Tenant $tenant, User $owner, ?Role $adminRole = null): TenantUser
    {
        $adminRole ??= Role::query()
            ->where('tenant_id', $tenant->id)
            ->where('name', 'tenant_admin')
            ->where('guard_name', 'web')
            ->firstOrFail();

        return TenantUser::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'user_id' => $owner->id,
            ],
            [
                'role_id' => $adminRole->id,
                'status' => 'active',
            ]
        );
    }

    public function setPrimaryDomain(Tenant $tenant, string $domain): TenantDomain
    {
        $normalizedDomain = $this->normalizeDomain($domain);

        $tenant->domains()->update(['is_primary' => false]);

        $primaryDomain = $tenant->domains()
            ->whereRaw('LOWER(domain) = ?', [strtolower($normalizedDomain)])
            ->first();

        if (! $primaryDomain) {
            $primaryDomain = $tenant->domains()->create([
                'domain' => $normalizedDomain,
                'is_primary' => true,
            ]);
        } else {
            $primaryDomain->update([
                'domain' => $normalizedDomain,
                'is_primary' => true,
            ]);
        }

        return $primaryDomain->fresh();
    }

    public function normalizeDomain(string $domain): string
    {
        $normalized = strtolower(trim($domain));

        $normalized = preg_replace('#^https?://#', '', $normalized) ?? $normalized;
        $normalized = preg_replace('#/.*$#', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/:\\d+$/', '', $normalized) ?? $normalized;

        return rtrim($normalized, '.');
    }

    public function enableAllGamesForTenant(Tenant $tenant): void
    {
        $gameIds = Game::query()->pluck('id');

        foreach ($gameIds as $gameId) {
            GameTenant::query()->updateOrCreate(
                [
                    'game_id' => (int) $gameId,
                    'tenant_id' => $tenant->id,
                ],
                [
                    'is_visible' => true,
                    'starts_at' => null,
                    'ends_at' => null,
                ]
            );
        }
    }
}
