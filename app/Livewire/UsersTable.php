<?php

namespace App\Livewire;

use App\Models\User;

class UsersTable extends BaseTable
{
    protected function model(): string
    {
        return User::class;
    }

    protected function columns(): array
    {
        return [
            [
                'label' => 'ID',
                'field' => 'id',
                'sortable' => true,
            ],
            [
                'label' => 'Name',
                'field' => 'name',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'label' => 'Email',
                'field' => 'email',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'label' => 'Superadmin',
                'field' => 'is_superadmin',
                'sortable' => true,
                'format' => fn($row) => $row->is_superadmin
                    ? '<span class="badge badge-light-success fs-7 fw-semibold">Yes</span>'
                    : '<span class="badge badge-light-secondary fs-7 fw-semibold">No</span>',
            ],
            [
                'label' => 'Created',
                'field' => 'created_at',
                'sortable' => true,
                'format' => fn($row) => $row->created_at->format('d/m/Y H:i'),
            ],
        ];
    }

    protected function actions($row): string
    {
        return view('users.partials.actions', ['user' => $row])->render();
    }
}
