<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Tenant;
use App\Support\Authorization\PermissionCatalog;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class TenantPermissionController extends Controller
{
    public function index(Request $request, TenantContext $tenantContext): View
    {
        $tenant = $this->tenantOrFail($tenantContext);

        $search = trim(strval($request->query('search', '')));
        $groupFilter = trim(strval($request->query('group', '')));
        $perPage = intval($request->query('per_page', 10));

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $roles = Role::query()
            ->where('tenant_id', $tenant->id)
            ->with([
                'permissions' => fn ($query) => $query->select('permissions.id', 'permissions.name'),
            ])
            ->get(['id', 'name']);

        $permissionRows = collect(PermissionCatalog::definitions())
            ->map(function (array $definition, string $permissionName) use ($roles): array {
                $assignedRoles = $roles
                    ->filter(fn (Role $role): bool => $role->permissions->contains('name', $permissionName))
                    ->pluck('name')
                    ->sort()
                    ->values()
                    ->all();

                return [
                    'name' => $permissionName,
                    'group' => strval($definition['group']),
                    'label' => strval($definition['label']),
                    'description' => strval($definition['description']),
                    'roles_count' => count($assignedRoles),
                    'role_names' => $assignedRoles,
                ];
            })
            ->values();

        $groupOptions = $permissionRows
            ->pluck('group')
            ->unique()
            ->sort()
            ->values()
            ->all();

        $permissions = $permissionRows
            ->when($search !== '', function ($rows) use ($search) {
                $searchLower = mb_strtolower($search);

                return $rows->filter(function (array $permission) use ($searchLower): bool {
                    $haystack = mb_strtolower(implode(' ', [
                        $permission['name'],
                        $permission['group'],
                        $permission['label'],
                        $permission['description'],
                        implode(' ', $permission['role_names']),
                    ]));

                    return str_contains($haystack, $searchLower);
                });
            })
            ->when($groupFilter !== '', fn ($rows) => $rows->where('group', $groupFilter))
            ->sortBy([
                ['group', 'asc'],
                ['label', 'asc'],
            ])
            ->values();

        $page = max(1, intval($request->query('page', 1)));
        $total = $permissions->count();
        $items = $permissions->forPage($page, $perPage)->values();

        $paginatedPermissions = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('access.permissions.index', [
            'permissions' => $paginatedPermissions,
            'search' => $search,
            'groupFilter' => $groupFilter,
            'groupOptions' => $groupOptions,
            'perPage' => $perPage,
        ]);
    }

    protected function tenantOrFail(TenantContext $tenantContext): Tenant
    {
        $tenant = $tenantContext->tenant();

        if (! $tenant) {
            abort(404, __('There is no active company for this domain.'));
        }

        return $tenant;
    }
}
