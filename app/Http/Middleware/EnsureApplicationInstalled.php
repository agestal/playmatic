<?php

namespace App\Http\Middleware;

use App\Support\Setup\InitialInstallationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApplicationInstalled
{
    public function __construct(
        protected InitialInstallationService $installationService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isTestingRuntime() || app()->runningInConsole()) {
            return $next($request);
        }

        if (! $this->installationService->hasRequiredTables()) {
            return $next($request);
        }

        $isInstalled = $this->installationService->isInstalled();
        $isInstallerRoute = $request->routeIs('install.*');

        if (! $isInstalled && ! $isInstallerRoute) {
            return redirect()->route('install.show', [
                'locale' => $request->route('locale') ?? config('app.locale'),
            ], absolute: false);
        }

        if ($isInstalled && $isInstallerRoute) {
            $target = $request->user() ? 'dashboard' : 'login';

            return redirect()->route($target, [
                'locale' => $request->route('locale') ?? config('app.locale'),
            ], absolute: false);
        }

        return $next($request);
    }

    protected function isTestingRuntime(): bool
    {
        return app()->runningUnitTests()
            || app()->environment('testing')
            || defined('PHPUNIT_COMPOSER_INSTALL')
            || defined('__PHPUNIT_PHAR__');
    }
}
