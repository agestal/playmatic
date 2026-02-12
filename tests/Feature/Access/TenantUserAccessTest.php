<?php

namespace Tests\Feature\Access;

use App\Http\Middleware\EnsureTenantMembership;
use App\Http\Middleware\RequireTenantPermission;
use App\Http\Middleware\ResolveTenantFromDomain;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use App\Support\Tenancy\TenantProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenantUserAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_user_when_email_does_not_exist_and_assigns_membership(): void
    {
        [$tenant, $adminRole, $admin] = $this->prepareTenantAccessContext();

        $response = $this
            ->actingAs($admin)
            ->post(route('users.store'), [
                'email' => 'NEW.User@Example.com',
                'password' => 'new-secret-password',
                'password_confirmation' => 'new-secret-password',
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
        $this->assertTrue(Hash::check('new-secret-password', $createdUser->password));

        $response->assertRedirectToRoute('users.index');

        $this->assertDatabaseHas('tenant_users', [
            'tenant_id' => $tenant->id,
            'user_id' => $createdUser->id,
            'role_id' => $adminRole->id,
            'status' => 'active',
        ]);
    }

    public function test_store_requires_password_when_creating_a_new_user(): void
    {
        [, $adminRole, $admin] = $this->prepareTenantAccessContext();

        $response = $this
            ->actingAs($admin)
            ->from(route('users.index'))
            ->post(route('users.store'), [
                'email' => 'new.no.password@example.com',
                'role_id' => $adminRole->id,
                'status' => 'active',
            ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', [
            'email' => 'new.no.password@example.com',
        ]);
    }

    public function test_store_updates_existing_user_password_when_it_is_provided(): void
    {
        [$tenant, $adminRole, $admin] = $this->prepareTenantAccessContext();

        $existingUser = User::factory()->create([
            'email' => 'existing.user@example.com',
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('users.store'), [
                'email' => 'existing.user@example.com',
                'password' => 'updated-login-password',
                'password_confirmation' => 'updated-login-password',
                'role_id' => $adminRole->id,
                'status' => 'active',
            ]);

        $response->assertRedirectToRoute('users.index');
        $response->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('updated-login-password', $existingUser->refresh()->password));

        $this->assertDatabaseHas('tenant_users', [
            'tenant_id' => $tenant->id,
            'user_id' => $existingUser->id,
            'role_id' => $adminRole->id,
            'status' => 'active',
        ]);
    }

    public function test_update_password_allows_changing_password_from_dedicated_route(): void
    {
        [$tenant, $adminRole, $admin] = $this->prepareTenantAccessContext();

        $memberUser = User::factory()->create();
        $membership = TenantUser::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $memberUser->id,
            'role_id' => $adminRole->id,
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($admin)
            ->put(route('users.password.update', ['tenantUser' => $membership->id]), [
                'password' => 'brand-new-password',
                'password_confirmation' => 'brand-new-password',
            ]);

        $response->assertRedirectToRoute('users.edit', ['tenantUser' => $membership->id]);
        $response->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('brand-new-password', $memberUser->refresh()->password));
    }

    /**
     * @return array{Tenant, \App\Models\Role, User}
     */
    protected function prepareTenantAccessContext(): array
    {
        $tenant = Tenant::query()->create([
            'name' => 'Acme',
            'slug' => 'acme',
        ]);

        $roles = app(TenantProvisioningService::class)->ensureDefaultRoles($tenant);
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

        return [$tenant, $adminRole, $admin];
    }
}
