<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Support\Authorization\PermissionCatalog;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenantRoleController extends Controller
{
    public function index(TenantContext $tenantContext): View
    {
        $this->tenantOrFail($tenantContext);

        return view('access.roles.index');
    }

    public function create(TenantContext $tenantContext): View
    {
        $tenant = $this->tenantOrFail($tenantContext);

        return view('access.roles.form', [
            'tenant' => $tenant,
            'role' => null,
            'permissionGroups' => PermissionCatalog::grouped(),
            'selectedPermissions' => [],
            'mode' => 'create',
        ]);
    }

    public function store(Request $request, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $validated = $this->validateRolePayload($request, $tenant->id);

        $role = Role::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()
            ->route('access.roles.index')
            ->with('status', 'Rol creado correctamente.');
    }

    public function edit(Role $role, TenantContext $tenantContext): View
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $this->assertRoleTenant($role, $tenant);

        return view('access.roles.form', [
            'tenant' => $tenant,
            'role' => $role->load('permissions:id,name'),
            'permissionGroups' => PermissionCatalog::grouped(),
            'selectedPermissions' => $role->permissions->pluck('name')->all(),
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, Role $role, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $this->assertRoleTenant($role, $tenant);

        $validated = $this->validateRolePayload($request, $tenant->id, $role);

        $role->update([
            'name' => $validated['name'],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()
            ->route('access.roles.index')
            ->with('status', 'Rol actualizado correctamente.');
    }

    public function destroy(Role $role, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $this->assertRoleTenant($role, $tenant);

        $isAssigned = TenantUser::query()
            ->where('tenant_id', $tenant->id)
            ->where('role_id', $role->id)
            ->exists();

        if ($isAssigned) {
            return redirect()
                ->route('access.roles.index')
                ->withErrors(['role' => 'No puedes eliminar un rol que ya esta asignado a usuarios.']);
        }

        $role->delete();

        return redirect()
            ->route('access.roles.index')
            ->with('status', 'Rol eliminado correctamente.');
    }

    /**
     * @return array{name:string,permissions?:array<int,string>}
     */
    protected function validateRolePayload(Request $request, int $tenantId, ?Role $role = null): array
    {
        $uniqueName = Rule::unique('roles', 'name')
            ->where(fn ($query) => $query
                ->where('tenant_id', $tenantId)
                ->where('guard_name', 'web'));

        if ($role) {
            $uniqueName->ignore($role->id);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:100', $uniqueName],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(PermissionCatalog::names())],
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

    protected function assertRoleTenant(Role $role, Tenant $tenant): void
    {
        if ((int) $role->tenant_id !== (int) $tenant->id) {
            abort(404);
        }
    }
}
