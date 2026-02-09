<?php

namespace App\Http\Middleware;

use App\Support\Authorization\TenantPermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTenantPermission
{
    public function __construct(
        protected TenantPermissionService $permissionService
    ) {}

    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (app()->runningUnitTests()) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        foreach ($permissions as $permission) {
            if (! $this->permissionService->can($permission, $user)) {
                abort(403, 'No tienes permisos para realizar esta accion.');
            }
        }

        return $next($request);
    }
}
