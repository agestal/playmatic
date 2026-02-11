<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\TenantUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_tenant_user_statistics(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Acme Corporation',
            'slug' => 'acme-corporation',
        ]);

        TenantDomain::query()->create([
            'tenant_id' => $tenant->id,
            'domain' => 'acme.playmatic.test',
            'is_primary' => true,
        ]);

        $role = Role::query()->create([
            'name' => 'tenant_admin',
            'guard_name' => 'web',
            'tenant_id' => $tenant->id,
        ]);

        $userOne = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $userTwo = User::factory()->create();
        $userThree = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        TenantUser::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $userOne->id,
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        TenantUser::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $userTwo->id,
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        TenantUser::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $userThree->id,
            'role_id' => $role->id,
            'status' => 'disabled',
        ]);

        DB::table('sessions')->insert([
            [
                'id' => (string) Str::uuid(),
                'user_id' => $userOne->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'phpunit',
                'payload' => base64_encode('payload-1'),
                'last_activity' => now()->subHours(2)->timestamp,
            ],
            [
                'id' => (string) Str::uuid(),
                'user_id' => $userTwo->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'phpunit',
                'payload' => base64_encode('payload-2'),
                'last_activity' => now()->subDays(2)->timestamp,
            ],
            [
                'id' => (string) Str::uuid(),
                'user_id' => $userThree->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'phpunit',
                'payload' => base64_encode('payload-3'),
                'last_activity' => now()->subHours(1)->timestamp,
            ],
        ]);

        $response = $this
            ->withServerVariables(['HTTP_HOST' => 'acme.playmatic.test'])
            ->actingAs($userOne)
            ->get(route('dashboard', ['locale' => 'en']));

        $response->assertOk();
        $response->assertViewHas('totalUsers', 3);
        $response->assertViewHas('activeUsers', 2);
        $response->assertViewHas('inactiveUsers', 1);
        $response->assertViewHas('verifiedUsers', 2);
        $response->assertViewHas('onlineLast24h', 1);
        $response->assertSeeText('Executive Dashboard');
        $response->assertSeeText('Total users');
    }
}
