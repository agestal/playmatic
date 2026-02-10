<?php

namespace App\Http\Middleware;

use App\Models\TenantDomain;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantFromDomain
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (app()->runningInConsole()) {
            $this->tenantContext->setTenant(null);

            return $next($request);
        }

        $host = strtolower($request->getHost());

        $tenantDomain = TenantDomain::query()
            ->whereRaw('LOWER(domain) = ?', [$host])
            ->with('tenant')
            ->first();

        if (! $tenantDomain && app()->environment('local') && config('playmatic.allow_unknown_domains_in_local', true)) {
            $tenantDomain = TenantDomain::query()
                ->with('tenant')
                ->orderByDesc('is_primary')
                ->orderBy('id')
                ->first();
        }

        if (! $tenantDomain && (app()->runningUnitTests() || app()->environment('testing'))) {
            $this->tenantContext->setTenant(null);

            return $next($request);
        }

        if (! $tenantDomain || ! $tenantDomain->tenant) {
            if ($this->canProceedWithoutTenant($request)) {
                $this->tenantContext->setTenant(null);
                View::share('currentTenant', null);

                return $next($request);
            }

            abort(404, 'No existe una empresa asociada al dominio '.$host.'.');
        }

        $this->tenantContext->setTenant($tenantDomain->tenant);
        View::share('currentTenant', $tenantDomain->tenant);

        return $next($request);
    }

    protected function canProceedWithoutTenant(Request $request): bool
    {
        return $request->routeIs('platform.*', 'login', 'logout', 'password.*', 'verification.*')
            || $request->is(
                'platform',
                'platform/*',
                'login',
                'logout',
                'forgot-password',
                'reset-password',
                'reset-password/*',
                'verify-email',
                'verify-email/*',
                'email/verification-notification',
                'confirm-password'
            );
    }
}
