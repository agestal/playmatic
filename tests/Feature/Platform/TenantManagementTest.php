<?php

namespace Tests\Feature\Platform;

use App\Models\Game;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Authorization\PermissionCatalog;
use App\Support\Tenancy\TenantProvisioningService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_only_superadmin_can_access_platform_tenants(): void
    {
        $user = User::factory()->create([
            'is_superadmin' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('platform.tenants.index'));

        $response->assertForbidden();
    }

    public function test_superadmin_can_create_tenant_with_primary_domain_owner_and_default_roles(): void
    {
        $game = Game::query()->create([
            'slug' => 'adivina-el-aforo',
            'name' => 'Adivina el aforo',
            'game_type' => 'attendance_guess',
            'is_active' => true,
        ]);

        $superadmin = User::factory()->create([
            'is_superadmin' => true,
        ]);

        $owner = User::factory()->create([
            'email' => 'owner@acme.local',
        ]);

        $response = $this
            ->actingAs($superadmin)
            ->post(route('platform.tenants.store'), [
                'name' => 'Acme Corporation',
                'slug' => 'acme-corporation',
                'primary_domain' => 'https://acme.playmatic.local/login',
                'owner_email' => $owner->email,
                'logo' => 'https://cdn.playmatic.local/logos/acme.svg',
                'primary_color' => '#0d6efd',
                'secondary_color' => '#20c997',
            ]);

        $tenant = Tenant::query()
            ->where('slug', 'acme-corporation')
            ->first();

        $this->assertNotNull($tenant);
        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'logo' => 'https://cdn.playmatic.local/logos/acme.svg',
            'primary_color' => '#0D6EFD',
            'secondary_color' => '#20C997',
        ]);

        $response->assertRedirect(route('platform.tenants.edit', ['tenant' => $tenant]));

        $this->assertDatabaseHas('tenant_domains', [
            'tenant_id' => $tenant->id,
            'domain' => 'acme.playmatic.local',
            'is_primary' => true,
        ]);

        $adminRole = Role::query()
            ->where('tenant_id', $tenant->id)
            ->where('name', 'tenant_admin')
            ->where('guard_name', 'web')
            ->first();

        $this->assertNotNull($adminRole);
        $this->assertSame(count(PermissionCatalog::names()), $adminRole->permissions()->count());

        $this->assertDatabaseHas('tenant_users', [
            'tenant_id' => $tenant->id,
            'user_id' => $owner->id,
            'role_id' => $adminRole->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('games_tenants', [
            'tenant_id' => $tenant->id,
            'game_id' => $game->id,
            'is_visible' => 1,
        ]);
    }

    public function test_superadmin_can_update_primary_domain_and_owner(): void
    {
        $superadmin = User::factory()->create([
            'is_superadmin' => true,
        ]);

        $oldOwner = User::factory()->create([
            'email' => 'old-owner@acme.local',
        ]);

        $newOwner = User::factory()->create([
            'email' => 'new-owner@acme.local',
        ]);

        $tenant = Tenant::query()->create([
            'name' => 'Acme',
            'slug' => 'acme',
        ]);

        $provisioningService = app(TenantProvisioningService::class);
        $roles = $provisioningService->ensureDefaultRoles($tenant);
        $provisioningService->assignOwner($tenant, $oldOwner, $roles->get('tenant_admin'));
        $provisioningService->setPrimaryDomain($tenant, 'old.playmatic.local');

        $response = $this
            ->actingAs($superadmin)
            ->put(route('platform.tenants.update', ['tenant' => $tenant]), [
                'name' => 'Acme Updated',
                'slug' => 'acme',
                'primary_domain' => 'new.playmatic.local',
                'owner_email' => $newOwner->email,
                'logo' => 'https://cdn.playmatic.local/logos/acme-updated.svg',
                'primary_color' => '#111827',
                'secondary_color' => '#f59e0b',
            ]);

        $response->assertRedirect(route('platform.tenants.edit', ['tenant' => $tenant]));

        $tenant->refresh();

        $this->assertSame('Acme Updated', $tenant->name);
        $this->assertSame('https://cdn.playmatic.local/logos/acme-updated.svg', $tenant->logo);
        $this->assertSame('#111827', $tenant->primary_color);
        $this->assertSame('#F59E0B', $tenant->secondary_color);

        $this->assertDatabaseHas('tenant_domains', [
            'tenant_id' => $tenant->id,
            'domain' => 'new.playmatic.local',
            'is_primary' => true,
        ]);

        $this->assertDatabaseHas('tenant_domains', [
            'tenant_id' => $tenant->id,
            'domain' => 'old.playmatic.local',
            'is_primary' => false,
        ]);

        $adminRole = Role::query()
            ->where('tenant_id', $tenant->id)
            ->where('name', 'tenant_admin')
            ->where('guard_name', 'web')
            ->firstOrFail();

        $this->assertDatabaseHas('tenant_users', [
            'tenant_id' => $tenant->id,
            'user_id' => $newOwner->id,
            'role_id' => $adminRole->id,
            'status' => 'active',
        ]);
    }
}
