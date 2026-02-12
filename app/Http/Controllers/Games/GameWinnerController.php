<?php

namespace App\Http\Controllers\Games;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameEntry;
use App\Models\GameWinner;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GameWinnerController extends Controller
{
    public function index(Request $request, TenantContext $tenantContext): View
    {
        $tenant = $this->tenantOrFail($tenantContext);

        $search = trim(strval($request->query('search', '')));
        $gameFilter = intval($request->query('game_id', 0));
        $perPage = intval($request->query('per_page', 10));

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $winners = GameWinner::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->with([
                'game:id,name,slug',
                'gameEntry:id,game_id,participant_name,participant_email,participant_user_id',
                'participantUser:id,name,email',
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $like = '%'.$search.'%';

                $query->where(function (Builder $searchQuery) use ($like): void {
                    $searchQuery
                        ->where('participant_name', 'like', $like)
                        ->orWhere('participant_email', 'like', $like)
                        ->orWhere('prize_name', 'like', $like)
                        ->orWhereHas('participantUser', fn (Builder $userQuery) => $userQuery
                            ->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like))
                        ->orWhereHas('game', fn (Builder $gameQuery) => $gameQuery
                            ->where('name', 'like', $like)
                            ->orWhere('slug', 'like', $like));
                });
            })
            ->when($gameFilter > 0, fn (Builder $query) => $query->where('game_id', $gameFilter))
            ->orderBy('position')
            ->orderByDesc('decided_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $gameOptions = $this->availableGamesQuery($tenant->id)
            ->get(['id', 'name'])
            ->map(fn (Game $game): array => [
                'id' => $game->id,
                'name' => $game->name,
            ])
            ->all();

        return view('games.winners.index', [
            'winners' => $winners,
            'search' => $search,
            'gameFilter' => $gameFilter,
            'gameOptions' => $gameOptions,
            'perPage' => $perPage,
        ]);
    }

    public function create(TenantContext $tenantContext): View
    {
        $tenant = $this->tenantOrFail($tenantContext);

        return view('games.winners.form', [
            'mode' => 'create',
            'winner' => null,
            'games' => $this->availableGames($tenant->id),
            'entries' => $this->entryOptions($tenant->id),
            'participants' => $this->participantOptions($tenant->id),
        ]);
    }

    public function store(Request $request, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $validated = $this->validatePayload($request, $tenant->id);

        $gameId = intval($validated['game_id']);
        $entryId = intval($validated['game_entry_id'] ?? 0);

        $this->assertGameVisibleForTenant($gameId, $tenant->id);

        $entry = $entryId > 0
            ? $this->entryForTenantOrFail($entryId, $gameId, $tenant->id)
            : null;

        $this->assertWinnerIntegrity($validated, $tenant->id, $gameId, null, $entry);

        $participantUser = isset($validated['participant_user_id'])
            ? User::query()->find(intval($validated['participant_user_id']))
            : $entry?->participantUser;

        GameWinner::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $gameId,
            'game_round_id' => $entry?->game_round_id,
            'game_entry_id' => $entry?->id,
            'participant_user_id' => $participantUser?->id,
            'participant_name' => data_get($validated, 'participant_name') ?: $entry?->participant_name ?: $participantUser?->name,
            'participant_email' => data_get($validated, 'participant_email') ?: $entry?->participant_email ?: $participantUser?->email,
            'position' => intval($validated['position']),
            'prize_name' => data_get($validated, 'prize_name'),
            'prize_value' => data_get($validated, 'prize_value'),
            'winner_payload' => $this->decodeJson($validated['winner_payload'] ?? null, 'winner_payload'),
            'notes' => data_get($validated, 'notes'),
            'decided_at' => $validated['decided_at'] ?? now(),
        ]);

        return redirect()
            ->route('games.winners.index')
            ->with('status', __('Game winner created successfully.'));
    }

    public function edit(string $locale, GameWinner $winner, TenantContext $tenantContext): View
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $this->assertWinnerTenant($winner, $tenant);

        return view('games.winners.form', [
            'mode' => 'edit',
            'winner' => $winner->load(['participantUser:id,name,email']),
            'games' => $this->availableGames($tenant->id),
            'entries' => $this->entryOptions($tenant->id),
            'participants' => $this->participantOptions($tenant->id),
        ]);
    }

    public function update(Request $request, string $locale, GameWinner $winner, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $this->assertWinnerTenant($winner, $tenant);

        $validated = $this->validatePayload($request, $tenant->id);

        $gameId = intval($validated['game_id']);
        $entryId = intval($validated['game_entry_id'] ?? 0);

        $this->assertGameVisibleForTenant($gameId, $tenant->id);

        $entry = $entryId > 0
            ? $this->entryForTenantOrFail($entryId, $gameId, $tenant->id)
            : null;

        $this->assertWinnerIntegrity($validated, $tenant->id, $gameId, $winner, $entry);

        $participantUser = isset($validated['participant_user_id'])
            ? User::query()->find(intval($validated['participant_user_id']))
            : $entry?->participantUser;

        $winner->update([
            'game_id' => $gameId,
            'game_round_id' => $entry?->game_round_id,
            'game_entry_id' => $entry?->id,
            'participant_user_id' => $participantUser?->id,
            'participant_name' => data_get($validated, 'participant_name') ?: $entry?->participant_name ?: $participantUser?->name,
            'participant_email' => data_get($validated, 'participant_email') ?: $entry?->participant_email ?: $participantUser?->email,
            'position' => intval($validated['position']),
            'prize_name' => data_get($validated, 'prize_name'),
            'prize_value' => data_get($validated, 'prize_value'),
            'winner_payload' => $this->decodeJson($validated['winner_payload'] ?? null, 'winner_payload'),
            'notes' => data_get($validated, 'notes'),
            'decided_at' => $validated['decided_at'] ?? now(),
        ]);

        return redirect()
            ->route('games.winners.index')
            ->with('status', __('Game winner updated successfully.'));
    }

    public function destroy(string $locale, GameWinner $winner, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $this->assertWinnerTenant($winner, $tenant);

        $winner->delete();

        return redirect()
            ->route('games.winners.index')
            ->with('status', __('Game winner deleted successfully.'));
    }

    /**
     * @return array{game_id:int|string,game_entry_id?:int|string|null,participant_user_id?:int|string|null,participant_name?:string,participant_email?:string,position:int|string,prize_name?:string,prize_value?:string,winner_payload?:string|null,notes?:string|null,decided_at?:string|null}
     */
    protected function validatePayload(Request $request, int $tenantId): array
    {
        return $request->validate([
            'game_id' => ['required', 'integer', Rule::exists('games', 'id')],
            'game_entry_id' => ['nullable', 'integer', Rule::exists('games_entries', 'id')],
            'participant_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'participant_name' => ['nullable', 'string', 'max:120'],
            'participant_email' => ['nullable', 'email', 'max:255'],
            'position' => ['required', 'integer', 'min:1', 'max:1000'],
            'prize_name' => ['nullable', 'string', 'max:160'],
            'prize_value' => ['nullable', 'string', 'max:160'],
            'winner_payload' => ['nullable', 'string', 'json'],
            'notes' => ['nullable', 'string', 'max:4000'],
            'decided_at' => ['nullable', 'date'],
        ]);
    }

    protected function assertWinnerIntegrity(
        array $validated,
        int $tenantId,
        int $gameId,
        ?GameWinner $winner,
        ?GameEntry $entry,
    ): void {
        $participantUserId = intval($validated['participant_user_id'] ?? 0);
        $participantName = trim(strval($validated['participant_name'] ?? ''));
        $participantEmail = trim(strval($validated['participant_email'] ?? ''));

        if ($participantUserId <= 0 && $participantName === '' && $participantEmail === '' && ! $entry) {
            throw ValidationException::withMessages([
                'participant_name' => __('Provide participant data or select an entry.'),
            ]);
        }

        if ($participantUserId > 0) {
            $isMember = TenantUser::query()
                ->where('tenant_id', $tenantId)
                ->where('user_id', $participantUserId)
                ->exists();

            if (! $isMember) {
                throw ValidationException::withMessages([
                    'participant_user_id' => __('The selected user is not part of the active tenant.'),
                ]);
            }
        }

        if ($entry) {
            $duplicatedWinnerQuery = GameWinner::query()
                ->withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('game_id', $gameId)
                ->where('game_entry_id', $entry->id);

            if ($winner) {
                $duplicatedWinnerQuery->where('id', '!=', $winner->id);
            }

            if ($duplicatedWinnerQuery->exists()) {
                throw ValidationException::withMessages([
                    'game_entry_id' => __('This entry is already assigned as a winner.'),
                ]);
            }
        }
    }

    protected function entryForTenantOrFail(int $entryId, int $gameId, int $tenantId): GameEntry
    {
        $entry = GameEntry::query()
            ->withoutGlobalScopes()
            ->where('id', $entryId)
            ->where('tenant_id', $tenantId)
            ->where('game_id', $gameId)
            ->with('participantUser:id,name,email')
            ->first();

        if (! $entry) {
            throw ValidationException::withMessages([
                'game_entry_id' => __('The selected entry is not valid for this game and tenant.'),
            ]);
        }

        return $entry;
    }

    protected function assertGameVisibleForTenant(int $gameId, int $tenantId): void
    {
        $exists = $this->availableGamesQuery($tenantId)
            ->where('games.id', $gameId)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'game_id' => __('The selected game is not visible for this tenant.'),
            ]);
        }
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
    protected function availableGames(int $tenantId): array
    {
        return $this->availableGamesQuery($tenantId)
            ->orderBy('games.name')
            ->get(['games.id', 'games.name'])
            ->map(fn (Game $game): array => [
                'id' => $game->id,
                'name' => $game->name,
            ])
            ->all();
    }

    protected function availableGamesQuery(int $tenantId): Builder
    {
        return Game::query()
            ->whereHas('tenantLinks', fn (Builder $query) => $query
                ->where('tenant_id', $tenantId)
                ->where('is_visible', true));
    }

    /**
     * @return array<int, array{id:int,label:string}>
     */
    protected function entryOptions(int $tenantId): array
    {
        return GameEntry::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->with(['game:id,name'])
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->limit(500)
            ->get(['id', 'game_id', 'participant_name', 'participant_email'])
            ->map(function (GameEntry $entry): array {
                $participant = $entry->participant_name ?: $entry->participant_email ?: ('#'.$entry->id);
                $gameName = $entry->game?->name ?: __('Unknown game');

                return [
                    'id' => $entry->id,
                    'label' => '#'.$entry->id.' - '.$gameName.' - '.$participant,
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array{id:int,label:string}>
     */
    protected function participantOptions(int $tenantId): array
    {
        return TenantUser::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with('user:id,name,email')
            ->orderBy('id')
            ->get(['id', 'user_id'])
            ->map(function (TenantUser $membership): ?array {
                if (! $membership->user) {
                    return null;
                }

                return [
                    'id' => intval($membership->user->id),
                    'label' => $membership->user->name.' <'.$membership->user->email.'>',
                ];
            })
            ->filter()
            ->values()
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

    protected function assertWinnerTenant(GameWinner $winner, Tenant $tenant): void
    {
        if ((int) $winner->tenant_id !== (int) $tenant->id) {
            abort(404);
        }
    }
}
