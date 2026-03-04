<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\TenantUser;
use App\Models\User;
use App\Support\Tenancy\TenantProvisioningService;
use Illuminate\Database\Eloquent\Builder;
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

    public function index(Request $request): View
    {
        $search = trim(strval($request->query('search', '')));
        $perPage = intval($request->query('per_page', 10));

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $primaryDomainSubquery = TenantDomain::query()
            ->select('domain')
            ->whereColumn('tenant_domains.tenant_id', 'tenants.id')
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->limit(1);

        $tenants = Tenant::query()
            ->select('tenants.*')
            ->selectSub($primaryDomainSubquery, 'primary_domain')
            ->withCount([
                'roles',
                'tenantUsers as active_users_count' => fn ($query) => $query->where('status', 'active'),
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $like = '%'.$search.'%';

                $query->where(function (Builder $searchQuery) use ($like): void {
                    $searchQuery
                        ->where('tenants.name', 'like', $like)
                        ->orWhere('tenants.slug', 'like', $like)
                        ->orWhereHas('domains', fn ($domainQuery) => $domainQuery->where('domain', 'like', $like));
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('platform.tenants.index', [
            'tenants' => $tenants,
            'search' => $search,
            'perPage' => $perPage,
        ]);
    }

    public function create(): View
    {
        [$gameOptions, $defaultGameIds] = $this->gameOptionsWithDefaults();

        return view('platform.tenants.form', [
            'tenant' => null,
            'mode' => 'create',
            'primaryDomain' => '',
            'ownerEmail' => '',
            'gameOptions' => $gameOptions,
            'selectedGameIds' => $defaultGameIds,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $tenant = DB::transaction(function () use ($validated): Tenant {
            $tenant = Tenant::query()->create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'logo' => $validated['logo'] ?? null,
                'primary_color' => $validated['primary_color'] ?? null,
                'secondary_color' => $validated['secondary_color'] ?? null,
            ]);

            $roles = $this->provisioningService->ensureDefaultRoles($tenant);

            $owner = User::query()
                ->where('email', $validated['owner_email'])
                ->firstOrFail();

            $this->provisioningService->assignOwner($tenant, $owner, $roles->get('admin'));
            $this->provisioningService->syncGamesForTenant($tenant, data_get($validated, 'game_ids', []));
            $this->provisioningService->setPrimaryDomain($tenant, $validated['primary_domain']);

            return $tenant;
        });

        return redirect()
            ->route('platform.tenants.edit', ['tenant' => $tenant])
            ->with('status', __('Tenant created successfully.'));
    }

    public function edit(string $locale, Tenant $tenant): View
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
                ->where('name', 'admin'))
            ->with('user:id,email')
            ->orderBy('id')
            ->first()?->user?->email ?? '';

        [$gameOptions] = $this->gameOptionsWithDefaults();

        $selectedGameIds = $tenant->games()
            ->select('games.id')
            ->pluck('games.id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();

        return view('platform.tenants.form', [
            'tenant' => $tenant,
            'mode' => 'edit',
            'primaryDomain' => $primaryDomain,
            'ownerEmail' => $ownerEmail,
            'gameOptions' => $gameOptions,
            'selectedGameIds' => $selectedGameIds,
        ]);
    }

    public function update(Request $request, string $locale, Tenant $tenant): RedirectResponse
    {
        $validated = $this->validatePayload($request, $tenant);

        DB::transaction(function () use ($validated, $tenant): void {
            $tenant->update([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'logo' => $validated['logo'] ?? null,
                'primary_color' => $validated['primary_color'] ?? null,
                'secondary_color' => $validated['secondary_color'] ?? null,
            ]);

            $roles = $this->provisioningService->ensureDefaultRoles($tenant);

            $owner = User::query()
                ->where('email', $validated['owner_email'])
                ->firstOrFail();

            $this->provisioningService->assignOwner($tenant, $owner, $roles->get('admin'));
            $this->provisioningService->syncGamesForTenant($tenant, data_get($validated, 'game_ids', []));
            $this->provisioningService->setPrimaryDomain($tenant, $validated['primary_domain']);
        });

        return redirect()
            ->route('platform.tenants.edit', ['tenant' => $tenant])
            ->with('status', __('Tenant updated successfully.'));
    }

    public function destroy(string $locale, Tenant $tenant): RedirectResponse
    {
        $tenantName = $tenant->name;

        $tenant->delete();

        return redirect()
            ->route('platform.tenants.index')
            ->with('status', __('Tenant :name deleted successfully.', ['name' => $tenantName]));
    }

    /**
     * @return array{name:string,slug:string,owner_email:string,primary_domain:string,logo:?string,primary_color:?string,secondary_color:?string,game_ids:array<int,int>}
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
            'logo' => ['nullable', 'string', 'max:2048'],
            'primary_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'game_ids_present' => ['nullable', 'boolean'],
            'game_ids' => ['nullable', 'array'],
            'game_ids.*' => ['integer', Rule::exists('games', 'id')],
        ]);

        $normalizedDomain = $this->provisioningService->normalizeDomain($validated['primary_domain']);

        if (! $this->isValidDomain($normalizedDomain)) {
            throw ValidationException::withMessages([
                'primary_domain' => __('The provided domain is not valid.'),
            ]);
        }

        $domainQuery = TenantDomain::query()
            ->whereRaw('LOWER(domain) = ?', [strtolower($normalizedDomain)]);

        if ($tenant) {
            $domainQuery->where('tenant_id', '!=', $tenant->id);
        }

        if ($domainQuery->exists()) {
            throw ValidationException::withMessages([
                'primary_domain' => __('That domain is already assigned to another tenant.'),
            ]);
        }

        $validated['primary_domain'] = $normalizedDomain;
        $validated['logo'] = $validated['logo'] ?? null;
        $validated['primary_color'] = isset($validated['primary_color']) ? strtoupper($validated['primary_color']) : null;
        $validated['secondary_color'] = isset($validated['secondary_color']) ? strtoupper($validated['secondary_color']) : null;
        $validated['game_ids'] = $this->resolveSelectedGameIds($request, $validated);

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

    /**
     * @return array{0:array<int,array{id:int,name:string,slug:string}>,1:array<int,int>}
     */
    protected function gameOptionsWithDefaults(): array
    {
        $games = Game::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $gameOptions = $games
            ->map(fn (Game $game): array => [
                'id' => (int) $game->id,
                'name' => $game->name,
                'slug' => $game->slug,
            ])
            ->all();

        $defaultGameIds = $games
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        return [$gameOptions, $defaultGameIds];
    }

    /**
     * @param array{
     *   game_ids_present?:bool|string|null,
     *   game_ids?:array<int,int|string>
     * } $validated
     * @return array<int,int>
     */
    protected function resolveSelectedGameIds(Request $request, array $validated): array
    {
        $rawIds = data_get($validated, 'game_ids', []);

        if (! is_array($rawIds)) {
            $rawIds = [];
        }

        $selectedIds = collect($rawIds)
            ->map(fn ($gameId): int => intval($gameId))
            ->filter(fn (int $gameId): bool => $gameId > 0)
            ->unique()
            ->values();

        if ($request->boolean('game_ids_present')) {
            return $selectedIds->all();
        }

        if ($selectedIds->isNotEmpty()) {
            return $selectedIds->all();
        }

        return Game::query()
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }
}
