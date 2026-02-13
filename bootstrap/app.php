<?php

use App\Http\Middleware\EnsureTenantMembership;
use App\Http\Middleware\EnsureApplicationInstalled;
use App\Http\Middleware\EnsureSuperadmin;
use App\Http\Middleware\RequireTenantPermission;
use App\Http\Middleware\ResolveTenantFromDomain;
use App\Http\Middleware\SetLocaleFromRoute;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prependToGroup('web', EnsureApplicationInstalled::class);
        $middleware->appendToGroup('web', ResolveTenantFromDomain::class);

        $middleware->alias([
            'set.locale' => SetLocaleFromRoute::class,
            'tenant.member' => EnsureTenantMembership::class,
            'tenant.permission' => RequireTenantPermission::class,
            'superadmin' => EnsureSuperadmin::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request): string {
            return route('login', [
                'locale' => $request->route('locale') ?? config('app.locale'),
            ], absolute: false);
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
