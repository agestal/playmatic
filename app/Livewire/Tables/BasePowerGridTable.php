<?php

namespace App\Livewire\Tables;

use App\Livewire\Tables\Concerns\HasStandardFilters;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

abstract class BasePowerGridTable extends PowerGridComponent
{
    use HasStandardFilters;

    protected bool $withCheckbox = false;

    protected bool $withSearch = true;

    protected bool $withPerPage = true;

    protected bool $withRecordCount = true;

    protected int $defaultPerPage = 10;

    /**
     * @var array<int>
     */
    protected array $perPageValues = [10, 25, 50, 100];

    public function setUp(): array
    {
        if ($this->withCheckbox) {
            $this->showCheckBox();
        }

        $header = PowerGrid::header();

        if ($this->withSearch) {
            $header->showSearchInput();
        }

        $footer = PowerGrid::footer();

        if ($this->withPerPage) {
            $footer->showPerPage($this->defaultPerPage, $this->perPageValues);
        }

        if ($this->withRecordCount) {
            $footer->showRecordCount();
        }

        return [$header, $footer];
    }
}
