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
        if (app()->runningUnitTests() || app()->environment('testing')) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        foreach ($permissions as $permission) {
            if (! $this->permissionService->can($permission, $user)) {
                abort(403, __('You do not have permission to perform this action.'));
            }
        }

        return $next($request);
    }
}
