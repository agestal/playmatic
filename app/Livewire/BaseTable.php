<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

abstract class BaseTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'id';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'asc'],
    ];

    // Métodos abstractos que cada tabla debe implementar
    abstract protected function model(): string;
    abstract protected function columns(): array;

    // Método opcional para acciones personalizadas
    protected function actions($row): string
    {
        return '';
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function getRowsProperty()
    {
        $model = $this->model();
        $query = $model::query();

        // Aplicar búsqueda
        if ($this->search) {
            $searchableColumns = collect($this->columns())
                ->where('searchable', true)
                ->pluck('field')
                ->toArray();

            $query->where(function ($q) use ($searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'like', '%' . $this->search . '%');
                }
            });
        }

        // Aplicar ordenamiento
        $query->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.base-table', [
            'rows' => $this->rows,
            'columns' => $this->columns(),
        ]);
    }
}
