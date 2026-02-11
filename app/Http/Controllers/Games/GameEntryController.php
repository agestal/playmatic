<?php

namespace App\Http\Controllers\Games;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameEntry;
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

class GameEntryController extends Controller
{
    public function index(Request $request, TenantContext $tenantContext): View
    {
        $tenant = $this->tenantOrFail($tenantContext);

        $search = trim(strval($request->query('search', '')));
        $statusFilter = trim(strval($request->query('status', '')));
        $gameFilter = intval($request->query('game_id', 0));
        $perPage = intval($request->query('per_page', 10));

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $entries = GameEntry::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->with([
                'game:id,name,slug',
                'participantUser:id,name,email',
                'winner:id,game_entry_id,position',
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $like = '%'.$search.'%';

                $query->where(function (Builder $searchQuery) use ($like): void {
                    $searchQuery
                        ->where('participant_name', 'like', $like)
                        ->orWhere('participant_email', 'like', $like)
                        ->orWhereHas('participantUser', fn (Builder $userQuery) => $userQuery
                            ->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like))
                        ->orWhereHas('game', fn (Builder $gameQuery) => $gameQuery
                            ->where('name', 'like', $like)
                            ->orWhere('slug', 'like', $like));
                });
            })
            ->when($statusFilter !== '', fn (Builder $query) => $query->where('status', $statusFilter))
            ->when($gameFilter > 0, fn (Builder $query) => $query->where('game_id', $gameFilter))
            ->orderByDesc('submitted_at')
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

        return view('games.entries.index', [
            'entries' => $entries,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'statusOptions' => $this->statusOptions(),
            'gameFilter' => $gameFilter,
            'gameOptions' => $gameOptions,
            'perPage' => $perPage,
        ]);
    }

    public function create(TenantContext $tenantContext): View
    {
        $tenant = $this->tenantOrFail($tenantContext);

        return view('games.entries.form', [
            'mode' => 'create',
            'entry' => null,
            'games' => $this->availableGames($tenant->id),
            'participants' => $this->participantOptions($tenant->id),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function store(Request $request, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $validated = $this->validatePayload($request, $tenant->id);

        $this->assertGameVisibleForTenant(intval($validated['game_id']), $tenant->id);
        $this->assertParticipantIntegrity($validated, $tenant->id);

        $participantUser = isset($validated['participant_user_id'])
            ? User::query()->find(intval($validated['participant_user_id']))
            : null;

        GameEntry::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'game_id' => intval($validated['game_id']),
            'participant_user_id' => $participantUser?->id,
            'participant_name' => data_get($validated, 'participant_name') ?: $participantUser?->name,
            'participant_email' => data_get($validated, 'participant_email') ?: $participantUser?->email,
            'status' => $validated['status'],
            'score' => data_get($validated, 'score') !== null ? floatval($validated['score']) : null,
            'answer_payload' => $this->decodeJson($validated['answer_payload'] ?? null, 'answer_payload'),
            'submitted_at' => $validated['submitted_at'] ?? now(),
            'evaluated_at' => $validated['evaluated_at'] ?? null,
        ]);

        return redirect()
            ->route('games.entries.index')
            ->with('status', __('Game entry created successfully.'));
    }

    public function edit(string $locale, GameEntry $entry, TenantContext $tenantContext): View
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $this->assertEntryTenant($entry, $tenant);

        return view('games.entries.form', [
            'mode' => 'edit',
            'entry' => $entry->load(['participantUser:id,name,email']),
            'games' => $this->availableGames($tenant->id),
            'participants' => $this->participantOptions($tenant->id),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function update(Request $request, string $locale, GameEntry $entry, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $this->assertEntryTenant($entry, $tenant);

        $validated = $this->validatePayload($request, $tenant->id);

        $this->assertGameVisibleForTenant(intval($validated['game_id']), $tenant->id);
        $this->assertParticipantIntegrity($validated, $tenant->id);

        $participantUser = isset($validated['participant_user_id'])
            ? User::query()->find(intval($validated['participant_user_id']))
            : null;

        $entry->update([
            'game_id' => intval($validated['game_id']),
            'participant_user_id' => $participantUser?->id,
            'participant_name' => data_get($validated, 'participant_name') ?: $participantUser?->name,
            'participant_email' => data_get($validated, 'participant_email') ?: $participantUser?->email,
            'status' => $validated['status'],
            'score' => data_get($validated, 'score') !== null ? floatval($validated['score']) : null,
            'answer_payload' => $this->decodeJson($validated['answer_payload'] ?? null, 'answer_payload'),
            'submitted_at' => $validated['submitted_at'] ?? now(),
            'evaluated_at' => $validated['evaluated_at'] ?? null,
        ]);

        return redirect()
            ->route('games.entries.index')
            ->with('status', __('Game entry updated successfully.'));
    }

    public function destroy(string $locale, GameEntry $entry, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $this->assertEntryTenant($entry, $tenant);

        $entry->delete();

        return redirect()
            ->route('games.entries.index')
            ->with('status', __('Game entry deleted successfully.'));
    }

    /**
     * @return array{game_id:int|string,participant_user_id?:int|string|null,participant_name?:string,participant_email?:string,status:string,score?:string|float|int|null,answer_payload?:string|null,submitted_at?:string|null,evaluated_at?:string|null}
     */
    protected function validatePayload(Request $request, int $tenantId): array
    {
        return $request->validate([
            'game_id' => ['required', 'integer', Rule::exists('games', 'id')],
            'participant_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'participant_name' => ['nullable', 'string', 'max:120'],
            'participant_email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', Rule::in(array_keys($this->statusOptions()))],
            'score' => ['nullable', 'numeric', 'min:0'],
            'answer_payload' => ['nullable', 'string', 'json'],
            'submitted_at' => ['nullable', 'date'],
            'evaluated_at' => ['nullable', 'date'],
        ]);
    }

    /**
     * @param  array{participant_user_id?:int|string|null,participant_name?:string|null,participant_email?:string|null}  $validated
     */
    protected function assertParticipantIntegrity(array $validated, int $tenantId): void
    {
        $participantUserId = intval($validated['participant_user_id'] ?? 0);
        $participantName = trim(strval($validated['participant_name'] ?? ''));
        $participantEmail = trim(strval($validated['participant_email'] ?? ''));

        if ($participantUserId <= 0 && $participantName === '' && $participantEmail === '') {
            throw ValidationException::withMessages([
                'participant_name' => __('Provide at least one participant reference (user, name or email).'),
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
     * @return array<string, string>
     */
    protected function statusOptions(): array
    {
        return [
            'submitted' => __('Submitted'),
            'evaluated' => __('Evaluated'),
            'invalid' => __('Invalid'),
            'winner' => __('Winner'),
        ];
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

    protected function assertEntryTenant(GameEntry $entry, Tenant $tenant): void
    {
        if ((int) $entry->tenant_id !== (int) $tenant->id) {
            abort(404);
        }
    }
}
