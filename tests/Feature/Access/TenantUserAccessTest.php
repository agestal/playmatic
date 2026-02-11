<?php

namespace Tests\Feature\Access;

use App\Http\Middleware\EnsureTenantMembership;
use App\Http\Middleware\RequireTenantPermission;
use App\Http\Middleware\ResolveTenantFromDomain;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use App\Support\Tenancy\TenantProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantUserAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_user_when_email_does_not_exist_and_assigns_membership(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Acme',
            'slug' => 'acme',
        ]);

        $provisioningService = app(TenantProvisioningService::class);
        $roles = $provisioningService->ensureDefaultRoles($tenant);
        $adminRole = $roles->get('tenant_admin');

        $this->assertNotNull($adminRole);

        $tenantContext = new TenantContext();
        $tenantContext->setTenant($tenant);
        $this->app->instance(TenantContext::class, $tenantContext);
        $this->withoutMiddleware([
            ResolveTenantFromDomain::class,
            EnsureTenantMembership::class,
            RequireTenantPermission::class,
        ]);

        $admin = User::factory()->create();

        $response = $this
            ->actingAs($admin)
            ->post(route('users.store'), [
                'email' => 'NEW.User@Example.com',
                'role_id' => $adminRole->id,
                'status' => 'active',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $createdUser = User::query()
            ->where('email', 'new.user@example.com')
            ->first();

        $this->assertNotNull($createdUser);
        $this->assertSame('New User', $createdUser->name);

        $response->assertRedirectToRoute('users.index');

        $this->assertDatabaseHas('tenant_users', [
            'tenant_id' => $tenant->id,
            'user_id' => $createdUser->id,
            'role_id' => $adminRole->id,
            'status' => 'active',
        ]);
    }
}
