<?php

use App\Http\Middleware\EnsureTenantMembership;
use App\Http\Middleware\RequireTenantPermission;
use App\Http\Middleware\ResolveTenantFromDomain;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', ResolveTenantFromDomain::class);

        $middleware->alias([
            'tenant.member' => EnsureTenantMembership::class,
            'tenant.permission' => RequireTenantPermission::class,
        ]);

        $middleware->redirectGuestsTo('/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
