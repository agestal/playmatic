<?php

namespace Tests\Feature\Tenancy;

use App\Http\Middleware\ResolveTenantFromDomain;
use App\Models\Tenant;
use App\Models\TenantDomain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class ResolveTenantFromDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_known_domain_resolves_tenant_and_applies_tenant_theme(): void
    {
        [$tenant, $domain] = $this->createTenantWithDomain(
            domain: 'globex.playmatic.test',
            primaryColor: '#0066CC',
            secondaryColor: '#E3F0FF'
        );

        $request = Request::create("http://{$domain}/en/login", 'GET');
        $this->assertSame($domain, $request->getHost());
        $this->assertDatabaseHas('tenant_domains', [
            'tenant_id' => $tenant->id,
            'domain' => $domain,
        ]);
        $this->assertNotNull(TenantDomain::query()
            ->whereRaw('LOWER(domain) = ?', [$domain])
            ->with('tenant')
            ->first());

        app(ResolveTenantFromDomain::class)->handle($request, fn (Request $request) => response('ok'));

        $sharedTenant = View::shared('currentTenant');
        $this->assertNotNull($sharedTenant);
        $this->assertSame($tenant->id, $sharedTenant?->id);

        $headHtml = view('layouts.metronic.partials.head')->render();

        $this->assertStringContainsString('--bs-primary: #0066CC;', $headHtml);
        $this->assertStringContainsString('--bs-secondary: #E3F0FF;', $headHtml);
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
}
