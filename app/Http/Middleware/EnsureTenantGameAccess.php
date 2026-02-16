<?php

namespace App\Http\Middleware;

use App\Models\Game;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantGameAccess
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    public function handle(Request $request, Closure $next, string $gameSlug): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if ((bool) $user->is_superadmin) {
            return $next($request);
        }

        $tenant = $this->tenantContext->tenant();

        if (! $tenant) {
            abort(404, __('There is no active company for this domain.'));
        }

        $now = now();

        $hasAccess = Game::query()
            ->where('slug', $gameSlug)
            ->where('is_active', true)
            ->whereHas('tenantLinks', function ($query) use ($tenant, $now): void {
                $query
                    ->where('tenant_id', $tenant->id)
                    ->where('is_visible', true)
                    ->where(function ($dateQuery) use ($now): void {
                        $dateQuery
                            ->whereNull('starts_at')
                            ->orWhere('starts_at', '<=', $now);
                    })
                    ->where(function ($dateQuery) use ($now): void {
                        $dateQuery
                            ->whereNull('ends_at')
                            ->orWhere('ends_at', '>=', $now);
                    });
            })
            ->exists();

        if (! $hasAccess) {
            abort(404);
        }

        return $next($request);
    }
}
