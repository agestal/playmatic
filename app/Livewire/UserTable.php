<?php

namespace App\Livewire;

use App\Livewire\Tables\BasePowerGridTable;
use App\Models\TenantUser;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class UserTable extends BasePowerGridTable
{
    public string $tableName = 'usersTable';

    protected bool $withCheckbox = true;

    public function datasource(): Builder
    {
        $query = User::query();

        $tenantId = app(TenantContext::class)->tenantId();

        if ($tenantId) {
            $query->whereHas('tenantMemberships', fn (Builder $membershipQuery) => $membershipQuery
                ->where('tenant_id', $tenantId));
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
            ->add('email')
            ->add('is_superadmin_badge', fn (User $user): string => $user->is_superadmin
                ? '<span class="kt-badge kt-badge-sm kt-badge-light kt-badge-success">Si</span>'
                : '<span class="kt-badge kt-badge-sm kt-badge-light">No</span>')
            ->add('created_at_formatted', fn (User $user): string => $user->created_at
                ? $user->created_at->format('d/m/Y H:i')
                : '-');
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable(),

            Column::make('Nombre', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Email', 'email')
                ->sortable()
                ->searchable(),

            Column::make('Superadmin', 'is_superadmin_badge', 'is_superadmin')
                ->sortable()
                ->searchable(),

            Column::make('Creado', 'created_at_formatted', 'created_at')
                ->sortable()
                ->searchable(),

            Column::action('Acciones')
        ];
    }

    #[\Livewire\Attributes\On('delete-user')]
    public function deleteUser(int $rowId): void
    {
        $tenantId = app(TenantContext::class)->tenantId();

        if (! $tenantId) {
            return;
        }

        if ((int) auth()->id() === $rowId && ! (bool) auth()->user()?->is_superadmin) {
            return;
        }

        TenantUser::query()
            ->where('tenant_id', $tenantId)
            ->where('user_id', $rowId)
            ->delete();
    }

    public function actions(User $row): array
    {
        return [
            Button::add('edit')
                ->slot('<i class="ki-outline ki-notepad-edit"></i>')
                ->class('kt-btn kt-btn-sm kt-btn-icon kt-btn-light')
                ->route('users.edit', ['user' => $row->id]),

            Button::add('delete')
                ->slot('<i class="ki-outline ki-trash"></i>')
                ->class('kt-btn kt-btn-sm kt-btn-icon kt-btn-light-danger')
                ->dispatch('delete-user', ['rowId' => $row->id])
                ->confirm('¿Quitar acceso de este usuario en la empresa actual?'),
        ];
    }
}
