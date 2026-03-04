<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(TenantContext $tenantContext): View
    {
        $tenant = $tenantContext->tenant();
        $games = $this->gamesDataset($tenant);

        $totals = [
            'active_games' => $games->where('is_active', true)->count(),
            'total_participants' => $games->sum('entries_count'),
            'total_winners' => $games->sum('winners_count'),
            'open_rounds' => $games->sum('open_rounds_count'),
            'total_plays' => $games->sum('plays_count'),
        ];

        return view('app.dashboard', [
            'tenant' => $tenant,
            'totals' => $totals,
            'gameStats' => $games,
        ]);
    }

    protected function gamesDataset(?Tenant $tenant)
    {
        $tenantId = $tenant?->id;

        if (! $tenantId) {
            return collect();
        }

        return Game::query()
            ->whereHas('tenantLinks', fn (Builder $query) => $query
                ->where('tenant_id', $tenantId)
                ->where('is_visible', true))
            ->withCount([
                'rounds as rounds_count' => fn (Builder $query) => $query->where('tenant_id', $tenantId),
                'entries as entries_count' => fn (Builder $query) => $query->where('tenant_id', $tenantId),
                'winners as winners_count' => fn (Builder $query) => $query->where('tenant_id', $tenantId),
                'rounds as open_rounds_count' => fn (Builder $query) => $query
                    ->where('tenant_id', $tenantId)
                    ->activeAt(),
            ])
            ->orderBy('name')
            ->get()
            ->map(function (Game $game): array {
                $roundsCount = intval($game->rounds_count ?? 0);
                $entriesCount = intval($game->entries_count ?? 0);

                return [
                    'id' => intval($game->id),
                    'name' => $game->name,
                    'slug' => $game->slug,
                    'is_active' => (bool) $game->is_active,
                    'rounds_count' => $roundsCount,
                    'entries_count' => $entriesCount,
                    'winners_count' => intval($game->winners_count ?? 0),
                    'open_rounds_count' => intval($game->open_rounds_count ?? 0),
                    // Generic definition: if the game tracks rounds/editions, use that.
                    // Otherwise fallback to entries so no game type needs custom code here.
                    'plays_count' => $roundsCount > 0 ? $roundsCount : $entriesCount,
                    'plays_basis' => $roundsCount > 0 ? 'rounds' : 'entries',
                ];
            });
    }
}
