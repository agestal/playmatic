<?php

namespace App\Http\Middleware;

use App\Models\TenantDomain;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantFromDomain
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Ensure shared tenant data never leaks between requests.
        View::share('currentTenant', null);

        $isTestingRuntime = $this->isTestingRuntime();

        if (app()->runningInConsole() && ! $isTestingRuntime) {
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

        if (! $tenantDomain && $isTestingRuntime) {
            $this->tenantContext->setTenant(null);

            return $next($request);
        }

        if (! $tenantDomain || ! $tenantDomain->tenant) {
            if ($this->canProceedWithoutTenant($request)) {
                $this->tenantContext->setTenant(null);

                return $next($request);
            }

            abort(404, __('No tenant is associated with the domain :domain.', ['domain' => $host]));
        }

        $this->tenantContext->setTenant($tenantDomain->tenant);
        View::share('currentTenant', $tenantDomain->tenant);

        return $next($request);
    }

    protected function canProceedWithoutTenant(Request $request): bool
    {
        if ($request->routeIs('platform.*', 'install.*', 'login', 'logout', 'password.*', 'verification.*')) {
            return true;
        }

        $normalizedPath = trim($request->path(), '/');
        $locale = $request->route('locale');

        if (is_string($locale) && $locale !== '') {
            if ($normalizedPath === $locale) {
                $normalizedPath = '';
            } elseif (str_starts_with($normalizedPath, $locale.'/')) {
                $normalizedPath = substr($normalizedPath, strlen($locale) + 1);
            }
        }

        return Str::is([
            'platform',
            'platform/*',
            'install',
            'install/*',
            'login',
            'logout',
            'forgot-password',
            'reset-password',
            'reset-password/*',
            'verify-email',
            'verify-email/*',
            'email/verification-notification',
            'confirm-password',
        ], $normalizedPath);
    }

    protected function isTestingRuntime(): bool
    {
        return app()->runningUnitTests()
            || app()->environment('testing')
            || defined('PHPUNIT_COMPOSER_INSTALL')
            || defined('__PHPUNIT_PHAR__');
    }
}
