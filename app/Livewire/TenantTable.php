<?php

namespace App\Livewire;

use App\Livewire\Tables\BasePowerGridTable;
use App\Models\Tenant;
use App\Models\TenantDomain;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class TenantTable extends BasePowerGridTable
{
    public string $tableName = 'tenantsTable';

    public function datasource(): Builder
    {
        return Tenant::query()
            ->select('tenants.*')
            ->selectSub(
                TenantDomain::query()
                    ->select('domain')
                    ->whereColumn('tenant_domains.tenant_id', 'tenants.id')
                    ->orderByDesc('is_primary')
                    ->orderBy('id')
                    ->limit(1),
                'primary_domain'
            )
            ->withCount('tenantUsers')
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
            ->add('slug')
            ->add('primary_domain', fn (Tenant $tenant): string => strval($tenant->getAttribute('primary_domain') ?: '-'))
            ->add('tenant_users_count_badge', fn (Tenant $tenant): string => '<span class="kt-badge kt-badge-sm kt-badge-light">'.$tenant->tenant_users_count.'</span>')
            ->add('created_at_formatted', fn (Tenant $tenant): string => $tenant->created_at
                ? $tenant->created_at->format('d/m/Y H:i')
                : '-');
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable(),

            Column::make('Tenant', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Slug', 'slug')
                ->sortable()
                ->searchable(),

            Column::make('Dominio primario', 'primary_domain')
                ->sortable()
                ->searchable(),

            Column::make('Usuarios', 'tenant_users_count_badge', 'tenant_users_count')
                ->sortable(),

            Column::make('Creado', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action('Acciones'),
        ];
    }

    #[\Livewire\Attributes\On('delete-tenant')]
    public function deleteTenant(int $rowId): void
    {
        if (! (bool) auth()->user()?->is_superadmin) {
            return;
        }

        Tenant::query()
            ->whereKey($rowId)
            ->delete();
    }

    public function actions(Tenant $row): array
    {
        return [
            Button::add('edit')
                ->slot('<i class="ki-outline ki-notepad-edit"></i>')
                ->class('kt-btn kt-btn-sm kt-btn-icon kt-btn-light')
                ->route('platform.tenants.edit', ['tenant' => $row->id]),

            Button::add('delete')
                ->slot('<i class="ki-outline ki-trash"></i>')
                ->class('kt-btn kt-btn-sm kt-btn-icon kt-btn-light-danger')
                ->dispatch('delete-tenant', ['rowId' => $row->id])
                ->confirm('¿Eliminar tenant y todos sus datos asociados?'),
        ];
    }
}
