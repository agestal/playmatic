<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenantUserAccessController extends Controller
{
    public function index(TenantContext $tenantContext): View
    {
        $this->tenantOrFail($tenantContext);

        return view('access.users.index');
    }

    public function store(Request $request, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $validated = $this->validateMembershipPayload($request, $tenant);

        $user = User::query()
            ->where('email', $validated['email'])
            ->firstOrFail();

        TenantUser::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
            ],
            [
                'role_id' => (int) $validated['role_id'],
                'status' => $validated['status'],
            ]
        );

        return redirect()
            ->route('access.users.index')
            ->with('status', 'Acceso de usuario actualizado para esta empresa.');
    }

    public function update(Request $request, TenantUser $tenantUser, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $this->assertMembershipTenant($tenantUser, $tenant);

        $validated = $request->validate([
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')->where(fn ($query) => $query->where('tenant_id', $tenant->id)),
            ],
            'status' => ['required', Rule::in(['active', 'disabled'])],
        ]);

        if ((int) $request->user()->id === (int) $tenantUser->user_id && $validated['status'] === 'disabled') {
            return redirect()
                ->route('access.users.index')
                ->withErrors(['membership' => 'No puedes desactivar tu propio acceso en esta empresa.']);
        }

        $tenantUser->update([
            'role_id' => (int) $validated['role_id'],
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('access.users.index')
            ->with('status', 'Permisos de usuario actualizados.');
    }

    public function destroy(Request $request, TenantUser $tenantUser, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $this->assertMembershipTenant($tenantUser, $tenant);

        if ((int) $request->user()->id === (int) $tenantUser->user_id) {
            return redirect()
                ->route('access.users.index')
                ->withErrors(['membership' => 'No puedes eliminar tu propio acceso desde aqui.']);
        }

        $tenantUser->delete();

        return redirect()
            ->route('access.users.index')
            ->with('status', 'Acceso eliminado correctamente.');
    }

    /**
     * @return array{email:string,role_id:int|string,status:string}
     */
    protected function validateMembershipPayload(Request $request, Tenant $tenant): array
    {
        return $request->validate([
            'email' => ['required', 'email', Rule::exists('users', 'email')],
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')->where(fn ($query) => $query->where('tenant_id', $tenant->id)),
            ],
            'status' => ['required', Rule::in(['active', 'disabled'])],
        ]);
    }

    protected function tenantOrFail(TenantContext $tenantContext): Tenant
    {
        $tenant = $tenantContext->tenant();

        if (! $tenant) {
            abort(404, 'No hay empresa activa en este dominio.');
        }

        return $tenant;
    }

    protected function assertMembershipTenant(TenantUser $tenantUser, Tenant $tenant): void
    {
        if ((int) $tenantUser->tenant_id !== (int) $tenant->id) {
            abort(404);
        }
    }
}
