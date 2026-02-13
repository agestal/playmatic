<?php

namespace App\Support\Setup;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\User;
use App\Support\Tenancy\TenantProvisioningService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class InitialInstallationService
{
    public function __construct(
        protected TenantProvisioningService $tenantProvisioningService
    ) {}

    public function hasRequiredTables(): bool
    {
        return Schema::hasTable('users')
            && Schema::hasTable('tenants')
            && Schema::hasTable('tenant_domains')
            && Schema::hasTable('tenant_users')
            && Schema::hasTable('roles');
    }

    public function isInstalled(): bool
    {
        if (! $this->hasRequiredTables()) {
            return false;
        }

        return User::query()->where('is_superadmin', true)->exists()
            && Tenant::query()->exists()
            && TenantDomain::query()->exists();
    }

    public function normalizeDomain(string $domain): string
    {
        return $this->tenantProvisioningService->normalizeDomain($domain);
    }

    public function isValidDomain(string $domain): bool
    {
        if ($domain === 'localhost') {
            return true;
        }

        return filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
    }

    public function emailExists(string $email): bool
    {
        return User::query()
            ->whereRaw('LOWER(email) = ?', [strtolower($email)])
            ->exists();
    }

    public function domainExists(string $domain): bool
    {
        return TenantDomain::query()
            ->whereRaw('LOWER(domain) = ?', [strtolower($domain)])
            ->exists();
    }

    /**
     * @param array{
     *   admin_name:string,
     *   admin_email:string,
     *   admin_password:string,
     *   tenant_name:string,
     *   primary_domain:string
     * } $payload
     * @return array{user:User,tenant:Tenant,domain:TenantDomain}
     */
    public function install(array $payload): array
    {
        return DB::transaction(function () use ($payload): array {
            $tenantName = trim($payload['tenant_name']);
            $adminName = trim($payload['admin_name']);
            $adminEmail = strtolower(trim($payload['admin_email']));
            $normalizedDomain = $this->normalizeDomain($payload['primary_domain']);

            $tenant = Tenant::query()->create([
                'name' => $tenantName,
                'slug' => $this->generateUniqueTenantSlug($tenantName),
                'logo' => null,
                'primary_color' => '#111827',
                'secondary_color' => '#3B82F6',
                'branding' => [
                    'logo' => null,
                    'primary_color' => '#111827',
                    'secondary_color' => '#3B82F6',
                ],
                'features' => [],
            ]);

            $user = new User();
            $user->name = $adminName;
            $user->email = $adminEmail;
            $user->password = Hash::make($payload['admin_password']);
            $user->is_superadmin = true;
            $user->email_verified_at = now();
            $user->save();

            $roles = $this->tenantProvisioningService->ensureDefaultRoles($tenant);
            $adminRole = $roles->get('tenant_admin');

            if (! $adminRole instanceof Role) {
                throw new RuntimeException('Unable to create tenant admin role during installation.');
            }

            $this->tenantProvisioningService->assignOwner($tenant, $user, $adminRole);
            $this->tenantProvisioningService->enableAllGamesForTenant($tenant);
            $domain = $this->tenantProvisioningService->setPrimaryDomain($tenant, $normalizedDomain);

            return [
                'user' => $user->fresh(),
                'tenant' => $tenant->fresh(),
                'domain' => $domain,
            ];
        });
    }

    protected function generateUniqueTenantSlug(string $tenantName): string
    {
        $base = Str::slug($tenantName);

        if ($base === '') {
            $base = 'tenant';
        }

        $slug = $base;
        $sequence = 2;

        while (Tenant::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$sequence;
            $sequence++;
        }

        return $slug;
    }
}
