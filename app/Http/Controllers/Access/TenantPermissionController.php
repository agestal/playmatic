<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Illuminate\View\View;

class TenantPermissionController extends Controller
{
    public function index(TenantContext $tenantContext): View
    {
        $this->tenantOrFail($tenantContext);

        return view('access.permissions.index');
    }

    protected function tenantOrFail(TenantContext $tenantContext): Tenant
    {
        $tenant = $tenantContext->tenant();

        if (! $tenant) {
            abort(404, 'No hay empresa activa en este dominio.');
        }

        return $tenant;
    }
}
