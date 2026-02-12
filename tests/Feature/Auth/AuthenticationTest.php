<?php

namespace Tests\Feature\Auth;

use App\Http\Middleware\ResolveTenantFromDomain;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\TenantUser;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
    }

    public function test_superadmins_can_not_authenticate_without_an_active_tenant_domain(): void
    {
        $user = User::factory()->create([
            'is_superadmin' => true,
        ]);

        $loginPath = route('login', absolute: false);

        $response = $this->from($loginPath)->post($loginPath, [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect($loginPath);
        $response->assertSessionHasErrors([
            'email' => __('There is no active company for this domain.'),
        ]);
        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_tenant_users_can_not_authenticate_without_an_active_tenant_domain(): void
    {
        $user = User::factory()->create();

        $loginPath = route('login', absolute: false);

        $response = $this->from($loginPath)->post($loginPath, [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect($loginPath);
        $response->assertSessionHasErrors([
            'email' => __('There is no active company for this domain.'),
        ]);
        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    public function test_users_can_not_authenticate_on_a_different_tenant_domain(): void
    {
        [$allowedTenant] = $this->createTenantWithDomain('acme.playmatic.test');
        [$blockedTenant] = $this->createTenantWithDomain('globex.playmatic.test');

        $user = User::factory()->create();
        $this->attachUserToTenant($user, $allowedTenant);

        $loginPath = route('login', absolute: false);

        $tenantContext = app(TenantContext::class);
        $tenantContext->setTenant($blockedTenant);
        $this->app->instance(TenantContext::class, $tenantContext);
        $this->withoutMiddleware(ResolveTenantFromDomain::class);

        $response = $this->from($loginPath)->post($loginPath, [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect($loginPath);
        $response->assertSessionHasErrors([
            'email' => __('Your user does not have access to this company on this domain.'),
        ]);
        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_on_a_different_tenant_domain_with_domain_resolution_enabled(): void
    {
        [$allowedTenant] = $this->createTenantWithDomain('acme.playmatic.test');
        [, $blockedDomain] = $this->createTenantWithDomain('globex.playmatic.test');

        $user = User::factory()->create();
        $this->attachUserToTenant($user, $allowedTenant);

        $loginPath = "http://{$blockedDomain}/".config('app.locale').'/login';

        $response = $this->from($loginPath)->post($loginPath, [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect($loginPath);
        $response->assertSessionHasErrors([
            'email' => __('Your user does not have access to this company on this domain.'),
        ]);
        $this->assertGuest();
    }

    public function test_superadmins_can_not_authenticate_on_a_tenant_domain_without_membership(): void
    {
        [, $tenantDomain] = $this->createTenantWithDomain('acme.playmatic.test');

        $user = User::factory()->create([
            'is_superadmin' => true,
        ]);

        $loginPath = "http://{$tenantDomain}/".config('app.locale').'/login';

        $response = $this->from($loginPath)->post($loginPath, [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect($loginPath);
        $response->assertSessionHasErrors([
            'email' => __('Your user does not have access to this company on this domain.'),
        ]);
        $this->assertGuest();
    }

    public function test_users_can_authenticate_on_their_tenant_domain(): void
    {
        [$tenant] = $this->createTenantWithDomain('acme.playmatic.test');

        $user = User::factory()->create();
        $this->attachUserToTenant($user, $tenant);

        $tenantContext = app(TenantContext::class);
        $tenantContext->setTenant($tenant);
        $this->app->instance(TenantContext::class, $tenantContext);
        $this->withoutMiddleware(ResolveTenantFromDomain::class);

        $response = $this->post(route('login', absolute: false), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    /**
     * @return array{Tenant,string}
     */
    protected function createTenantWithDomain(
        string $domain,
        string $primaryColor = '#1B84FF',
        string $secondaryColor = '#F1F1F4'
    ): array {
        $sequence = Tenant::query()->count() + 1;

        $tenant = Tenant::query()->create([
            'name' => "Tenant {$sequence}",
            'slug' => "tenant-{$sequence}",
            'primary_color' => strtoupper($primaryColor),
            'secondary_color' => strtoupper($secondaryColor),
        ]);

        TenantDomain::query()->create([
            'tenant_id' => $tenant->id,
            'domain' => $domain,
            'is_primary' => true,
        ]);

        return [$tenant, $domain];
    }

    protected function attachUserToTenant(User $user, Tenant $tenant): void
    {
        $role = Role::query()->create([
            'tenant_id' => $tenant->id,
            'name' => "tenant_member_{$tenant->id}_{$user->id}",
            'guard_name' => 'web',
        ]);

        TenantUser::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'role_id' => $role->id,
            'status' => 'active',
        ]);
    }
}
