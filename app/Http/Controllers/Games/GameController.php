<?php

namespace App\Http\Controllers\Games;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameEntry;
use App\Models\GameTenant;
use App\Models\GameWinner;
use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GameController extends Controller
{
    public function index(Request $request, TenantContext $tenantContext): View
    {
        $tenant = $this->tenantOrFail($tenantContext);

        $search = trim(strval($request->query('search', '')));
        $typeFilter = trim(strval($request->query('type', '')));
        $perPage = intval($request->query('per_page', 10));

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $games = Game::query()
            ->whereHas('tenantLinks', function (Builder $query) use ($tenant): void {
                $query
                    ->where('tenant_id', $tenant->id)
                    ->where('is_visible', true);
            })
            ->withCount([
                'entries as entries_count' => fn (Builder $query) => $query->where('tenant_id', $tenant->id),
                'winners as winners_count' => fn (Builder $query) => $query->where('tenant_id', $tenant->id),
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $like = '%'.$search.'%';

                $query->where(function (Builder $searchQuery) use ($like): void {
                    $searchQuery
                        ->where('games.name', 'like', $like)
                        ->orWhere('games.slug', 'like', $like)
                        ->orWhere('games.game_type', 'like', $like);
                });
            })
            ->when($typeFilter !== '', fn (Builder $query) => $query->where('game_type', $typeFilter))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $typeOptions = Game::query()
            ->whereHas('tenantLinks', fn (Builder $query) => $query
                ->where('tenant_id', $tenant->id)
                ->where('is_visible', true))
            ->orderBy('game_type')
            ->pluck('game_type')
            ->unique()
            ->values()
            ->all();

        return view('games.index', [
            'games' => $games,
            'search' => $search,
            'typeFilter' => $typeFilter,
            'typeOptions' => $typeOptions,
            'perPage' => $perPage,
        ]);
    }

    public function create(TenantContext $tenantContext): View
    {
        $tenant = $this->tenantOrFail($tenantContext);

        return view('games.form', [
            'mode' => 'create',
            'game' => null,
            'tenantOptions' => $this->tenantOptions($tenant),
            'selectedTenantIds' => [$tenant->id],
            'isSuperadmin' => (bool) auth()->user()?->is_superadmin,
        ]);
    }

    public function store(Request $request, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $validated = $this->validatePayload($request, $tenant);

        DB::transaction(function () use ($validated, $tenant): void {
            $game = Game::query()->create([
                'slug' => $validated['slug'],
                'name' => $validated['name'],
                'game_type' => $validated['game_type'],
                'description' => $validated['description'] ?? null,
                'is_active' => (bool) ($validated['is_active'] ?? true),
                'config' => $this->decodeJson($validated['config_json'] ?? null, 'config_json'),
            ]);

            $this->syncTenantVisibility($game, $tenant, $validated['tenant_ids'] ?? []);
        });

        return redirect()
            ->route('games.index')
            ->with('status', __('Game created successfully.'));
    }

    public function edit(string $locale, Game $game, TenantContext $tenantContext): View
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $this->assertGameIsVisibleForTenant($game, $tenant);

        $selectedTenantIds = $game->tenantLinks()
            ->orderBy('tenant_id')
            ->pluck('tenant_id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        if (! auth()->user()?->is_superadmin) {
            $selectedTenantIds = [$tenant->id];
        }

        return view('games.form', [
            'mode' => 'edit',
            'game' => $game,
            'tenantOptions' => $this->tenantOptions($tenant),
            'selectedTenantIds' => $selectedTenantIds,
            'isSuperadmin' => (bool) auth()->user()?->is_superadmin,
        ]);
    }

    public function update(Request $request, string $locale, Game $game, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $this->assertGameIsVisibleForTenant($game, $tenant);

        $validated = $this->validatePayload($request, $tenant, $game);

        DB::transaction(function () use ($game, $validated, $tenant): void {
            $game->update([
                'slug' => $validated['slug'],
                'name' => $validated['name'],
                'game_type' => $validated['game_type'],
                'description' => $validated['description'] ?? null,
                'is_active' => (bool) ($validated['is_active'] ?? true),
                'config' => $this->decodeJson($validated['config_json'] ?? null, 'config_json'),
            ]);

            $this->syncTenantVisibility($game, $tenant, $validated['tenant_ids'] ?? []);
        });

        return redirect()
            ->route('games.index')
            ->with('status', __('Game updated successfully.'));
    }

    public function destroy(string $locale, Game $game, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $this->assertGameIsVisibleForTenant($game, $tenant);

        DB::transaction(function () use ($game, $tenant): void {
            GameTenant::query()
                ->where('game_id', $game->id)
                ->where('tenant_id', $tenant->id)
                ->delete();

            GameWinner::query()
                ->withoutGlobalScopes()
                ->where('game_id', $game->id)
                ->where('tenant_id', $tenant->id)
                ->delete();

            GameEntry::query()
                ->withoutGlobalScopes()
                ->where('game_id', $game->id)
                ->where('tenant_id', $tenant->id)
                ->delete();

            $hasVisibleTenants = GameTenant::query()
                ->where('game_id', $game->id)
                ->exists();

            if (! $hasVisibleTenants) {
                $game->delete();
            }
        });

        return redirect()
            ->route('games.index')
            ->with('status', __('Game removed successfully.'));
    }

    /**
     * @return array{name:string,slug:string,game_type:string,description?:string,is_active?:bool,config_json?:string,tenant_ids?:array<int,int|string>}
     */
    protected function validatePayload(Request $request, Tenant $tenant, ?Game $game = null): array
    {
        $slugRule = Rule::unique('games', 'slug');

        if ($game) {
            $slugRule->ignore($game->id);
        }

        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'alpha_dash', 'max:120', $slugRule],
            'game_type' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:4000'],
            'is_active' => ['nullable', 'boolean'],
            'config_json' => ['nullable', 'string', 'json'],
        ];

        if (auth()->user()?->is_superadmin) {
            $rules['tenant_ids'] = ['required', 'array', 'min:1'];
            $rules['tenant_ids.*'] = ['integer', Rule::exists('tenants', 'id')];
        }

        $validated = $request->validate($rules);

        if (! auth()->user()?->is_superadmin) {
            $validated['tenant_ids'] = [$tenant->id];
        }

        if (empty($validated['tenant_ids'])) {
            throw ValidationException::withMessages([
                'tenant_ids' => __('At least one tenant must be selected.'),
            ]);
        }

        return $validated;
    }

    /**
     * @param  array<int, int|string>  $tenantIds
     */
    protected function syncTenantVisibility(Game $game, Tenant $tenant, array $tenantIds): void
    {
        if (! auth()->user()?->is_superadmin) {
            GameTenant::query()->updateOrCreate(
                [
                    'game_id' => $game->id,
                    'tenant_id' => $tenant->id,
                ],
                [
                    'is_visible' => true,
                    'starts_at' => null,
                    'ends_at' => null,
                ]
            );

            return;
        }

        $syncData = collect($tenantIds)
            ->map(fn ($tenantId): int => intval($tenantId))
            ->filter(fn (int $tenantId): bool => $tenantId > 0)
            ->unique()
            ->mapWithKeys(fn (int $tenantId): array => [
                $tenantId => [
                    'is_visible' => true,
                    'starts_at' => null,
                    'ends_at' => null,
                ],
            ])
            ->all();

        $game->tenants()->sync($syncData);
    }

    protected function decodeJson(?string $json, string $field): ?array
    {
        if (! is_string($json) || trim($json) === '') {
            return null;
        }

        /** @var mixed $decoded */
        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            throw ValidationException::withMessages([
                $field => __('The :field field must contain a valid JSON object.', ['field' => $field]),
            ]);
        }

        return $decoded;
    }

    /**
     * @return array<int, array{id:int,name:string}>
     */
    protected function tenantOptions(Tenant $tenant): array
    {
        if (! auth()->user()?->is_superadmin) {
            return [
                [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                ],
            ];
        }

        return Tenant::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Tenant $row): array => [
                'id' => $row->id,
                'name' => $row->name,
            ])
            ->all();
    }

    protected function tenantOrFail(TenantContext $tenantContext): Tenant
    {
        $tenant = $tenantContext->tenant();

        if (! $tenant) {
            abort(404, __('There is no active company for this domain.'));
        }

        return $tenant;
    }

    protected function assertGameIsVisibleForTenant(Game $game, Tenant $tenant): void
    {
        $exists = GameTenant::query()
            ->where('game_id', $game->id)
            ->where('tenant_id', $tenant->id)
            ->exists();

        if (! $exists) {
            abort(404);
        }
    }
}
