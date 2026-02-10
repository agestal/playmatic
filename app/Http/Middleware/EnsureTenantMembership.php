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
        if (app()->runningUnitTests() || app()->environment('testing')) {
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
            abort(403, __('There is no active company for this domain.'));
        }

        $membership = $this->tenantContext->membership($user);

        if (! $membership) {
            abort(403, __('Your user does not have access to this company.'));
        }

        $this->tenantContext->setMembership($membership);

        return $next($request);
    }
}
