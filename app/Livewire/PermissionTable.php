<?php

namespace App\Livewire;

use App\Livewire\Tables\BasePowerGridTable;
use App\Support\Authorization\PermissionCatalog;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use Spatie\Permission\Models\Permission;

final class PermissionTable extends BasePowerGridTable
{
    public string $tableName = 'permissionsTable';

    public function datasource(): Builder
    {
        return Permission::query()
            ->withCount('roles')
            ->orderBy('name');
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
            ->add('group_name', fn (Permission $permission): string => $this->permissionMeta($permission->name)['group'] ?? '-')
            ->add('label_name', fn (Permission $permission): string => $this->permissionMeta($permission->name)['label'] ?? '-')
            ->add('roles_count_badge', fn (Permission $permission): string => '<span class="kt-badge kt-badge-sm kt-badge-light">'.$permission->roles_count.'</span>')
            ->add('created_at_formatted', fn (Permission $permission): string => $permission->created_at
                ? $permission->created_at->format('d/m/Y H:i')
                : '-');
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable(),

            Column::make('Permiso', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Guard', 'guard_name')
                ->sortable()
                ->searchable(),

            Column::make('Grupo', 'group_name'),

            Column::make('Etiqueta', 'label_name'),

            Column::make('Roles', 'roles_count_badge', 'roles_count')
                ->sortable(),

            Column::make('Creado', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action('Acciones'),
        ];
    }

    public function actions(Permission $row): array
    {
        return [
            Button::add('view-roles')
                ->slot('<i class="ki-outline ki-eye"></i>')
                ->class('kt-btn kt-btn-sm kt-btn-icon kt-btn-light')
                ->route('access.roles.index', ['permission' => $row->name]),
        ];
    }

    /**
     * @return array{group?:string,label?:string,description?:string}
     */
    private function permissionMeta(string $permissionName): array
    {
        return PermissionCatalog::definitions()[$permissionName] ?? [];
    }
}
