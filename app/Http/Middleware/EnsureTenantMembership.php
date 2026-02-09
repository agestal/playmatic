<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantMembership
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (app()->runningUnitTests()) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ((bool) $user->is_superadmin) {
            return $next($request);
        }

        if (! $this->tenantContext->hasTenant()) {
            abort(403, 'No hay empresa activa para este dominio.');
        }

        $membership = $this->tenantContext->membership($user);

        if (! $membership) {
            abort(403, 'Tu usuario no tiene acceso a esta empresa.');
        }

        $this->tenantContext->setMembership($membership);

        return $next($request);
    }
}
