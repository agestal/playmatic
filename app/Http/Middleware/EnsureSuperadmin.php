<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperadmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if (! (bool) $user->is_superadmin) {
            abort(403, __('Only superadmin users can access this section.'));
        }

        return $next($request);
    }
}
