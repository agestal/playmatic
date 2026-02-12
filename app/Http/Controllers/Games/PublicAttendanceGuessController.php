<?php

namespace App\Http\Controllers\Games;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameEntry;
use App\Models\GameRound;
use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicAttendanceGuessController extends Controller
{
    protected const GAME_SLUG = 'adivina-el-aforo';

    public function show(TenantContext $tenantContext): View
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $game = $this->attendanceGameForTenant($tenant);

        $activeRound = $game
            ? $this->activeRoundForTenantGame($tenant->id, $game->id)
            : null;

        return view('games.public.attendance-guess', [
            'tenant' => $tenant,
            'game' => $game,
            'activeRound' => $activeRound,
            'thirdConsentLabel' => '[PENDIENTE: texto del tercer consentimiento]',
        ]);
    }

    public function store(Request $request, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $game = $this->attendanceGameForTenant($tenant);

        if (! $game) {
            return redirect()
                ->route('public.attendance-guess.show')
                ->withErrors(['contest' => __('There is no active contest at the moment.')]);
        }

        $activeRound = $this->activeRoundForTenantGame($tenant->id, $game->id);

        if (! $activeRound) {
            return redirect()
                ->route('public.attendance-guess.show')
                ->withErrors(['contest' => __('There is no active contest at the moment.')]);
        }

        $validated = $request->validate([
            'participant_name' => ['required', 'string', 'max:120'],
            'participant_phone' => ['required', 'string', 'max:40'],
            'participant_email' => ['required', 'email', 'max:255'],
            'attendance_guess' => ['required', 'integer', 'min:0', 'max:999999999'],
            'accept_terms' => ['accepted'],
            'accept_marketing' => ['nullable', 'boolean'],
            'accept_third' => ['nullable', 'boolean'],
        ]);

        GameEntry::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $game->id,
            'game_round_id' => $activeRound->id,
            'participant_name' => trim($validated['participant_name']),
            'participant_email' => trim($validated['participant_email']),
            'participant_phone' => trim($validated['participant_phone']),
            'status' => 'submitted',
            'score' => null,
            'answer_payload' => [
                'attendance_guess' => intval($validated['attendance_guess']),
                'consents' => [
                    'terms' => true,
                    'marketing' => $request->boolean('accept_marketing'),
                    'third' => $request->boolean('accept_third'),
                ],
                'submitted_from' => [
                    'ip' => $request->ip(),
                    'user_agent' => substr(strval($request->userAgent()), 0, 500),
                ],
            ],
            'submitted_at' => now(),
            'evaluated_at' => null,
        ]);

        return redirect()
            ->route('public.attendance-guess.show')
            ->with('status', __('Your participation has been registered successfully.'));
    }

    protected function activeRoundForTenantGame(int $tenantId, int $gameId): ?GameRound
    {
        return GameRound::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('game_id', $gameId)
            ->activeAt()
            ->orderByDesc('activated_at')
            ->orderByDesc('starts_at')
            ->orderByDesc('id')
            ->first();
    }

    protected function attendanceGameForTenant(Tenant $tenant): ?Game
    {
        return Game::query()
            ->where('slug', self::GAME_SLUG)
            ->where('is_active', true)
            ->whereHas('tenantLinks', fn (Builder $query) => $query
                ->where('tenant_id', $tenant->id)
                ->where('is_visible', true))
            ->first();
    }

    protected function tenantOrFail(TenantContext $tenantContext): Tenant
    {
        $tenant = $tenantContext->tenant();

        if (! $tenant) {
            abort(404, __('There is no active company for this domain.'));
        }

        return $tenant;
    }
}
