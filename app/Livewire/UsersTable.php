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
                'label' => __('ID'),
                'field' => 'id',
                'sortable' => true,
            ],
            [
                'label' => __('Name'),
                'field' => 'name',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'label' => __('Email'),
                'field' => 'email',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'label' => __('Superadmin'),
                'field' => 'is_superadmin',
                'sortable' => true,
                'format' => fn($row) => $row->is_superadmin
                    ? '<span class="kt-badge kt-badge-success kt-badge-sm">'.e(__('Yes')).'</span>'
                    : '<span class="kt-badge kt-badge-sm">'.e(__('No')).'</span>',
            ],
            [
                'label' => __('Created'),
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
