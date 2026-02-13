<?php

namespace Tests\Feature\Setup;

use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use App\Support\Setup\InitialInstallationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InstallationTest extends TestCase
{
    use RefreshDatabase;

    public function test_install_screen_can_be_rendered_when_application_is_not_installed(): void
    {
        $response = $this->get(route('install.show'));

        $response->assertOk();
        $response->assertSeeText(__('Initial setup'));
    }

    public function test_install_creates_superadmin_tenant_primary_domain_and_membership(): void
    {
        $response = $this->post(route('install.store'), [
            'admin_name' => 'Root Admin',
            'admin_email' => 'root@example.com',
            'admin_password' => 'strong-password-123',
            'admin_password_confirmation' => 'strong-password-123',
            'tenant_name' => 'Playmatic Global',
            'primary_domain' => 'https://App.Example.com/login',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $admin = User::query()->where('email', 'root@example.com')->first();
        $this->assertNotNull($admin);
        $this->assertTrue((bool) $admin->is_superadmin);
        $this->assertTrue(Hash::check('strong-password-123', $admin->password));

        $tenant = Tenant::query()->where('name', 'Playmatic Global')->first();
        $this->assertNotNull($tenant);

        $this->assertDatabaseHas('tenant_domains', [
            'tenant_id' => $tenant->id,
            'domain' => 'app.example.com',
            'is_primary' => 1,
        ]);

        $membership = TenantUser::query()
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $admin->id)
            ->with('role')
            ->first();

        $this->assertNotNull($membership);
        $this->assertSame('active', $membership->status);
        $this->assertSame('tenant_admin', $membership->role?->name);
    }

    public function test_install_screen_redirects_when_application_is_already_installed(): void
    {
        app(InitialInstallationService::class)->install([
            'admin_name' => 'Existing Admin',
            'admin_email' => 'existing-admin@example.com',
            'admin_password' => 'existing-password-123',
            'tenant_name' => 'Existing Tenant',
            'primary_domain' => 'existing.example.com',
        ]);

        $response = $this->get(route('install.show'));

        $response->assertRedirect(route('login'));
    }
}
