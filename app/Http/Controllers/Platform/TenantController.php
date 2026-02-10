<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\TenantUser;
use App\Models\User;
use App\Support\Tenancy\TenantProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function __construct(
        protected TenantProvisioningService $provisioningService
    ) {}

    public function index(): View
    {
        return view('platform.tenants.index');
    }

    public function create(): View
    {
        return view('platform.tenants.form', [
            'tenant' => null,
            'mode' => 'create',
            'primaryDomain' => '',
            'ownerEmail' => '',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $tenant = DB::transaction(function () use ($validated): Tenant {
            $tenant = Tenant::query()->create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
            ]);

            $roles = $this->provisioningService->ensureDefaultRoles($tenant);

            $owner = User::query()
                ->where('email', $validated['owner_email'])
                ->firstOrFail();

            $this->provisioningService->assignOwner($tenant, $owner, $roles->get('tenant_admin'));
            $this->provisioningService->setPrimaryDomain($tenant, $validated['primary_domain']);

            return $tenant;
        });

        return redirect()
            ->route('platform.tenants.edit', $tenant)
            ->with('status', 'Tenant creado correctamente.');
    }

    public function edit(Tenant $tenant): View
    {
        $tenant->load([
            'domains' => fn ($query) => $query
                ->orderByDesc('is_primary')
                ->orderBy('id'),
        ]);

        $primaryDomain = $tenant->domains
            ->firstWhere('is_primary', true)?->domain
            ?? $tenant->domains->first()?->domain
            ?? '';

        $ownerEmail = TenantUser::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->whereHas('role', fn ($query) => $query
                ->where('tenant_id', $tenant->id)
                ->where('name', 'tenant_admin'))
            ->with('user:id,email')
            ->orderBy('id')
            ->first()?->user?->email ?? '';

        return view('platform.tenants.form', [
            'tenant' => $tenant,
            'mode' => 'edit',
            'primaryDomain' => $primaryDomain,
            'ownerEmail' => $ownerEmail,
        ]);
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $this->validatePayload($request, $tenant);

        DB::transaction(function () use ($validated, $tenant): void {
            $tenant->update([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
            ]);

            $roles = $this->provisioningService->ensureDefaultRoles($tenant);

            $owner = User::query()
                ->where('email', $validated['owner_email'])
                ->firstOrFail();

            $this->provisioningService->assignOwner($tenant, $owner, $roles->get('tenant_admin'));
            $this->provisioningService->setPrimaryDomain($tenant, $validated['primary_domain']);
        });

        return redirect()
            ->route('platform.tenants.edit', $tenant)
            ->with('status', 'Tenant actualizado correctamente.');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $tenantName = $tenant->name;

        $tenant->delete();

        return redirect()
            ->route('platform.tenants.index')
            ->with('status', 'Tenant '.$tenantName.' eliminado correctamente.');
    }

    /**
     * @return array{name:string,slug:string,owner_email:string,primary_domain:string}
     */
    protected function validatePayload(Request $request, ?Tenant $tenant = null): array
    {
        $slugRule = Rule::unique('tenants', 'slug');

        if ($tenant) {
            $slugRule->ignore($tenant->id);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'alpha_dash', 'max:100', $slugRule],
            'owner_email' => ['required', 'email', Rule::exists('users', 'email')],
            'primary_domain' => ['required', 'string', 'max:255'],
        ]);

        $normalizedDomain = $this->provisioningService->normalizeDomain($validated['primary_domain']);

        if (! $this->isValidDomain($normalizedDomain)) {
            throw ValidationException::withMessages([
                'primary_domain' => 'El dominio indicado no es valido.',
            ]);
        }

        $domainQuery = TenantDomain::query()
            ->whereRaw('LOWER(domain) = ?', [strtolower($normalizedDomain)]);

        if ($tenant) {
            $domainQuery->where('tenant_id', '!=', $tenant->id);
        }

        if ($domainQuery->exists()) {
            throw ValidationException::withMessages([
                'primary_domain' => 'Ese dominio ya esta asignado a otro tenant.',
            ]);
        }

        $validated['primary_domain'] = $normalizedDomain;

        return $validated;
    }

    protected function isValidDomain(string $domain): bool
    {
        if (strlen($domain) > 253) {
            return false;
        }

        if (! str_contains($domain, '.')) {
            return false;
        }

        return (bool) preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9-]{2,63}$/i', $domain);
    }
}
