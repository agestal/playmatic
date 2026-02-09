<?php

namespace App\Support\PowerGridThemes;

use PowerComponents\LivewirePowerGrid\Themes\Tailwind;

class MetronicTheme extends Tailwind
{
    public function layout(): array
    {
        return [
            'table' => 'livewire-powergrid::components.frameworks.tailwind.table-base',
            'header' => 'powergrid.metronic.header',
            'pagination' => 'powergrid.metronic.pagination',
            'footer' => 'powergrid.metronic.footer',
        ];
    }

    public function table(): array
    {
        return array_replace_recursive(parent::table(), [
            'layout' => [
                'base' => 'w-full',
                'div' => 'kt-card-table',
                'table' => 'kt-table kt-table-border power-grid-table',
                'container' => 'w-full',
                'actions' => 'flex items-center gap-1',
            ],

            'header' => [
                'thead' => 'pg-table-head',
                'tr' => '',
                'th' => 'whitespace-nowrap text-xs font-semibold uppercase tracking-wide text-gray-700',
                'thAction' => 'text-end',
            ],

            'body' => [
                'tbody' => '',
                'tbodyEmpty' => '',
                'tr' => '',
                'td' => '',
                'tdEmpty' => 'py-10 text-center',
                'tdSummarize' => 'text-sm text-secondary-foreground text-end',
                'trSummarize' => '',
                'tdFilters' => '',
                'trFilters' => '',
                'tdActionsContainer' => 'flex items-center justify-end gap-1',
            ],
        ]);
    }

    public function footer(): array
    {
        return array_replace(parent::footer(), [
            'view' => data_get($this->layout(), 'footer'),
            'select' => 'kt-select kt-select-sm min-w-[90px]',
            'footer' => 'kt-card-footer flex flex-wrap items-center justify-between gap-3',
            'footer_with_pagination' => '',
        ]);
    }

    public function cols(): array
    {
        return [
            'div' => 'kt-table-col',
        ];
    }

    public function filterBoolean(): array
    {
        return array_replace(parent::filterBoolean(), [
            'base' => 'min-w-[7rem]',
            'select' => 'kt-select kt-select-sm w-full',
        ]);
    }

    public function filterDatePicker(): array
    {
        return array_replace(parent::filterDatePicker(), [
            'input' => 'kt-input kt-input-sm w-full',
        ]);
    }

    public function filterNumber(): array
    {
        return array_replace(parent::filterNumber(), [
            'input' => 'kt-input kt-input-sm w-full',
        ]);
    }

    public function filterSelect(): array
    {
        return array_replace(parent::filterSelect(), [
            'select' => 'kt-select kt-select-sm w-full',
        ]);
    }

    public function filterInputText(): array
    {
        return array_replace(parent::filterInputText(), [
            'select' => 'kt-select kt-select-sm w-full',
            'input' => 'kt-input kt-input-sm w-full',
        ]);
    }

    public function searchBox(): array
    {
        return array_replace(parent::searchBox(), [
            'input' => 'kt-input kt-input-sm h-9 ps-8 pe-8 w-full',
            'iconClose' => 'text-gray-500 hover:text-gray-700',
            'iconSearch' => 'w-4 h-4 text-gray-500',
        ]);
    }
}
