<?php

namespace App\Livewire;

use App\Livewire\Tables\BasePowerGridTable;
use App\Models\Role;
use App\Models\TenantUser;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class RoleTable extends BasePowerGridTable
{
    public string $tableName = 'rolesTable';

    public ?string $permission = null;

    /**
     * @var array<string, array{except:null}>
     */
    protected array $queryString = [
        'permission' => ['except' => null],
    ];

    public function datasource(): Builder
    {
        $tenantId = app(TenantContext::class)->tenantId();

        $query = Role::query()
            ->withCount(['permissions', 'tenantUsers'])
            ->orderBy('name');

        if (filled($this->permission)) {
            $query->whereHas('permissions', fn (Builder $permissionQuery) => $permissionQuery
                ->where('name', $this->permission));
        }

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('guard_name')
            ->add('permissions_count_badge', fn (Role $role): string => '<span class="kt-badge kt-badge-sm kt-badge-light">'.$role->permissions_count.'</span>')
            ->add('tenant_users_count_badge', fn (Role $role): string => '<span class="kt-badge kt-badge-sm kt-badge-light">'.$role->tenant_users_count.'</span>')
            ->add('created_at_formatted', fn (Role $role): string => $role->created_at
                ? $role->created_at->format('d/m/Y H:i')
                : '-');
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable(),

            Column::make('Rol', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Guard', 'guard_name')
                ->sortable()
                ->searchable(),

            Column::make('Permisos', 'permissions_count_badge', 'permissions_count')
                ->sortable(),

            Column::make('Usuarios', 'tenant_users_count_badge', 'tenant_users_count')
                ->sortable(),

            Column::make('Creado', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action('Acciones'),
        ];
    }

    #[\Livewire\Attributes\On('delete-role')]
    public function deleteRole(int $rowId): void
    {
        $tenantId = app(TenantContext::class)->tenantId();

        if (! $tenantId) {
            return;
        }

        $isAssigned = TenantUser::query()
            ->where('tenant_id', $tenantId)
            ->where('role_id', $rowId)
            ->exists();

        if ($isAssigned) {
            return;
        }

        Role::query()
            ->where('id', $rowId)
            ->where('tenant_id', $tenantId)
            ->delete();
    }

    public function actions(Role $row): array
    {
        return [
            Button::add('edit')
                ->slot('<i class="ki-outline ki-notepad-edit"></i>')
                ->class('kt-btn kt-btn-sm kt-btn-icon kt-btn-light')
                ->route('access.roles.edit', ['role' => $row->id]),

            Button::add('delete')
                ->slot('<i class="ki-outline ki-trash"></i>')
                ->class('kt-btn kt-btn-sm kt-btn-icon kt-btn-light-danger')
                ->dispatch('delete-role', ['rowId' => $row->id])
                ->confirm('¿Eliminar este rol?'),
        ];
    }
}
