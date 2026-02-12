<?php

namespace App\Http\Controllers\Games;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameEntry;
use App\Models\GameRound;
use App\Models\GameWinner;
use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AttendanceGuessRoundController extends Controller
{
    protected const GAME_SLUG = 'adivina-el-aforo';

    public function index(Request $request, TenantContext $tenantContext): View
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $game = $this->attendanceGameOrFail($tenant);

        $search = trim(strval($request->query('search', '')));
        $statusFilter = trim(strval($request->query('status', '')));
        $perPage = intval($request->query('per_page', 10));

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $now = now();

        $rounds = GameRound::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('game_id', $game->id)
            ->withCount('entries')
            ->when($search !== '', fn (Builder $query) => $query->where('name', 'like', '%'.$search.'%'))
            ->when($statusFilter !== '', fn (Builder $query) => $this->applyStatusFilter($query, $statusFilter, $now))
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('games.attendance-rounds.index', [
            'rounds' => $rounds,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'statusOptions' => $this->statusOptions(),
            'perPage' => $perPage,
            'publicUrl' => url('/adivina-aforo'),
        ]);
    }

    public function create(TenantContext $tenantContext): View
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $this->attendanceGameOrFail($tenant);

        return view('games.attendance-rounds.form', [
            'mode' => 'create',
            'round' => null,
            'managementModeOptions' => $this->managementModeOptions(),
        ]);
    }

    public function store(Request $request, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $game = $this->attendanceGameOrFail($tenant);

        $validated = $this->validatePayload($request);
        $attributes = $this->normalizePayload($validated);

        if ($attributes['management_mode'] === 'scheduled') {
            $this->assertNoScheduledOverlap(
                $tenant->id,
                $game->id,
                $attributes['starts_at'],
                $attributes['ends_at'],
            );

            if ($this->isScheduledActiveNow($attributes['starts_at'], $attributes['ends_at'])) {
                $this->assertNoActiveRoundConflict($tenant->id, $game->id);
            }
        }

        GameRound::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $game->id,
            'name' => $attributes['name'],
            'management_mode' => $attributes['management_mode'],
            'starts_at' => $attributes['starts_at'],
            'ends_at' => $attributes['ends_at'],
            'activated_at' => null,
            'deactivated_at' => null,
            'result_value' => $attributes['result_value'],
            'result_recorded_at' => $attributes['result_value'] !== null ? now() : null,
        ]);

        return redirect()
            ->route('games.attendance-rounds.index')
            ->with('status', __('Round created successfully.'));
    }

    public function edit(string $locale, GameRound $round, TenantContext $tenantContext): View
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $game = $this->attendanceGameOrFail($tenant);

        $this->assertRoundBelongsToTenantGame($round, $tenant->id, $game->id);

        return view('games.attendance-rounds.form', [
            'mode' => 'edit',
            'round' => $round,
            'managementModeOptions' => $this->managementModeOptions(),
        ]);
    }

    public function update(Request $request, string $locale, GameRound $round, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $game = $this->attendanceGameOrFail($tenant);

        $this->assertRoundBelongsToTenantGame($round, $tenant->id, $game->id);

        $validated = $this->validatePayload($request);
        $attributes = $this->normalizePayload($validated);

        $nextActivatedAt = $attributes['management_mode'] === 'manual' ? $round->activated_at : null;
        $nextDeactivatedAt = $round->deactivated_at;

        if ($attributes['management_mode'] === 'scheduled') {
            $this->assertNoScheduledOverlap(
                $tenant->id,
                $game->id,
                $attributes['starts_at'],
                $attributes['ends_at'],
                $round->id,
            );

            $willBeActiveNow = $this->isScheduledActiveNow($attributes['starts_at'], $attributes['ends_at'])
                && $nextDeactivatedAt === null;

            if ($willBeActiveNow) {
                $this->assertNoActiveRoundConflict($tenant->id, $game->id, $round->id);
            }
        } elseif ($nextActivatedAt && $nextDeactivatedAt === null) {
            $this->assertNoActiveRoundConflict($tenant->id, $game->id, $round->id);
        }

        $resultRecordedAt = null;

        if ($attributes['result_value'] !== null) {
            if ((int) $round->result_value !== (int) $attributes['result_value'] || ! $round->result_recorded_at) {
                $resultRecordedAt = now();
            } else {
                $resultRecordedAt = $round->result_recorded_at;
            }
        }

        $round->update([
            'name' => $attributes['name'],
            'management_mode' => $attributes['management_mode'],
            'starts_at' => $attributes['starts_at'],
            'ends_at' => $attributes['ends_at'],
            'activated_at' => $nextActivatedAt,
            'deactivated_at' => $nextDeactivatedAt,
            'result_value' => $attributes['result_value'],
            'result_recorded_at' => $resultRecordedAt,
        ]);

        return redirect()
            ->route('games.attendance-rounds.index')
            ->with('status', __('Round updated successfully.'));
    }

    public function activate(string $locale, GameRound $round, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $game = $this->attendanceGameOrFail($tenant);

        $this->assertRoundBelongsToTenantGame($round, $tenant->id, $game->id);

        if ($round->management_mode !== 'manual') {
            throw ValidationException::withMessages([
                'round' => __('Only manually managed rounds can be activated manually.'),
            ]);
        }

        if (! $round->isActiveAt()) {
            $this->assertNoActiveRoundConflict($tenant->id, $game->id, $round->id);
        }

        $round->update([
            'activated_at' => now(),
            'deactivated_at' => null,
        ]);

        return redirect()
            ->route('games.attendance-rounds.index')
            ->with('status', __('Round activated successfully.'));
    }

    public function deactivate(string $locale, GameRound $round, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $game = $this->attendanceGameOrFail($tenant);

        $this->assertRoundBelongsToTenantGame($round, $tenant->id, $game->id);

        $round->update([
            'deactivated_at' => now(),
        ]);

        return redirect()
            ->route('games.attendance-rounds.index')
            ->with('status', __('Round deactivated successfully.'));
    }

    public function destroy(string $locale, GameRound $round, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $game = $this->attendanceGameOrFail($tenant);

        $this->assertRoundBelongsToTenantGame($round, $tenant->id, $game->id);

        DB::transaction(function () use ($round, $tenant): void {
            GameWinner::query()
                ->withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('game_round_id', $round->id)
                ->delete();

            GameEntry::query()
                ->withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('game_round_id', $round->id)
                ->delete();

            $round->delete();
        });

        return redirect()
            ->route('games.attendance-rounds.index')
            ->with('status', __('Round deleted successfully.'));
    }

    /**
     * @return array{name:string,management_mode:string,starts_at?:string|null,ends_at?:string|null,result_value?:string|int|null}
     */
    protected function validatePayload(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'management_mode' => ['required', 'in:manual,scheduled'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'result_value' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($validated['management_mode'] === 'scheduled') {
            if (empty($validated['starts_at']) || empty($validated['ends_at'])) {
                throw ValidationException::withMessages([
                    'starts_at' => __('Automatic mode requires start and end dates.'),
                ]);
            }
        }

        return $validated;
    }

    /**
     * @param  array{name:string,management_mode:string,starts_at?:string|null,ends_at?:string|null,result_value?:string|int|null}  $validated
     * @return array{name:string,management_mode:string,starts_at:mixed,ends_at:mixed,result_value:int|null}
     */
    protected function normalizePayload(array $validated): array
    {
        $mode = $validated['management_mode'];

        $startsAt = null;
        $endsAt = null;

        if ($mode === 'scheduled') {
            $startsAt = $validated['starts_at'] ? Carbon::parse($validated['starts_at']) : null;
            $endsAt = $validated['ends_at'] ? Carbon::parse($validated['ends_at']) : null;
        }

        return [
            'name' => trim($validated['name']),
            'management_mode' => $mode,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'result_value' => array_key_exists('result_value', $validated) && $validated['result_value'] !== null
                ? intval($validated['result_value'])
                : null,
        ];
    }

    protected function applyStatusFilter(Builder $query, string $status, CarbonInterface $now): void
    {
        if ($status === 'active') {
            $query->activeAt($now);

            return;
        }

        if ($status === 'pending') {
            $query->where(function (Builder $pendingQuery) use ($now): void {
                $pendingQuery
                    ->where(function (Builder $manualQuery): void {
                        $manualQuery
                            ->where('management_mode', 'manual')
                            ->whereNull('activated_at')
                            ->whereNull('deactivated_at');
                    })
                    ->orWhere(function (Builder $scheduledQuery) use ($now): void {
                        $scheduledQuery
                            ->where('management_mode', 'scheduled')
                            ->whereNotNull('starts_at')
                            ->where('starts_at', '>', $now)
                            ->whereNull('deactivated_at');
                    });
            });

            return;
        }

        if ($status === 'closed') {
            $query->where(function (Builder $closedQuery) use ($now): void {
                $closedQuery
                    ->whereNotNull('deactivated_at')
                    ->orWhere(function (Builder $scheduledQuery) use ($now): void {
                        $scheduledQuery
                            ->where('management_mode', 'scheduled')
                            ->whereNotNull('ends_at')
                            ->where('ends_at', '<', $now)
                            ->whereNull('deactivated_at');
                    });
            });
        }
    }

    protected function assertNoScheduledOverlap(
        int $tenantId,
        int $gameId,
        CarbonInterface $startsAt,
        CarbonInterface $endsAt,
        ?int $excludeRoundId = null,
    ): void {
        $conflictQuery = GameRound::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('game_id', $gameId)
            ->where('management_mode', 'scheduled')
            ->whereNull('deactivated_at')
            ->whereNotNull('starts_at')
            ->whereNotNull('ends_at')
            ->where('starts_at', '<=', $endsAt)
            ->where('ends_at', '>=', $startsAt);

        if ($excludeRoundId) {
            $conflictQuery->where('id', '!=', $excludeRoundId);
        }

        if ($conflictQuery->exists()) {
            throw ValidationException::withMessages([
                'starts_at' => __('The scheduled range overlaps with another round.'),
            ]);
        }
    }

    protected function assertNoActiveRoundConflict(int $tenantId, int $gameId, ?int $excludeRoundId = null): void
    {
        $activeQuery = GameRound::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('game_id', $gameId)
            ->activeAt();

        if ($excludeRoundId) {
            $activeQuery->where('id', '!=', $excludeRoundId);
        }

        if ($activeQuery->exists()) {
            throw ValidationException::withMessages([
                'round' => __('There is already another active round for this game.'),
            ]);
        }
    }

    protected function isScheduledActiveNow(?CarbonInterface $startsAt, ?CarbonInterface $endsAt): bool
    {
        if (! $startsAt || ! $endsAt) {
            return false;
        }

        $now = now();

        return $startsAt <= $now && $endsAt >= $now;
    }

    /**
     * @return array<string, string>
     */
    protected function statusOptions(): array
    {
        return [
            'active' => __('Active'),
            'pending' => __('Pending'),
            'closed' => __('Closed'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function managementModeOptions(): array
    {
        return [
            'manual' => __('Manual activation'),
            'scheduled' => __('Automatic by schedule'),
        ];
    }

    protected function tenantOrFail(TenantContext $tenantContext): Tenant
    {
        $tenant = $tenantContext->tenant();

        if (! $tenant) {
            abort(404, __('There is no active company for this domain.'));
        }

        return $tenant;
    }

    protected function attendanceGameOrFail(Tenant $tenant): Game
    {
        $game = Game::query()
            ->where('slug', self::GAME_SLUG)
            ->where('is_active', true)
            ->whereHas('tenantLinks', fn (Builder $query) => $query
                ->where('tenant_id', $tenant->id)
                ->where('is_visible', true))
            ->first();

        if (! $game) {
            abort(404, __('Attendance Guess game is not available for this tenant.'));
        }

        return $game;
    }

    protected function assertRoundBelongsToTenantGame(GameRound $round, int $tenantId, int $gameId): void
    {
        if ((int) $round->tenant_id !== $tenantId || (int) $round->game_id !== $gameId) {
            abort(404);
        }
    }
}
