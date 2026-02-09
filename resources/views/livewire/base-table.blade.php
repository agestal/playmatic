<div>
    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-4 pb-5 border-b border-gray-200 mb-5">
        {{-- Búsqueda --}}
        <div class="relative flex-1 max-w-md">
            <i class="ki-outline ki-magnifier absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-lg"></i>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                class="form-control form-control-solid ps-12"
                placeholder="Search users..."
            >
            @if($search)
                <button
                    wire:click="$set('search', '')"
                    type="button"
                    class="btn btn-sm btn-icon btn-active-light-primary position-absolute end-0 top-50 translate-middle-y me-2"
                >
                    <i class="ki-outline ki-cross fs-2"></i>
                </button>
            @endif
        </div>

        {{-- Per page selector --}}
        <div class="d-flex align-items-center gap-2">
            <span class="text-gray-700 fs-6 fw-semibold">Show</span>
            <select wire:model.live="perPage" class="form-select form-select-sm form-select-solid w-75px">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span class="text-gray-700 fs-6 fw-semibold">entries</span>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="table-responsive">
        <table class="table table-row-bordered table-hover align-middle gs-0 gy-3">
            <thead>
                <tr class="fw-bold text-muted bg-light">
                    @foreach($columns as $column)
                        <th class="min-w-125px ps-4 {{ $column['sortable'] ?? false ? 'cursor-pointer' : '' }}"
                            @if($column['sortable'] ?? false)
                                wire:click="sortBy('{{ $column['field'] }}')"
                            @endif
                        >
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-uppercase fs-7 fw-bold">{{ $column['label'] }}</span>
                                @if($column['sortable'] ?? false)
                                    <span class="ms-1">
                                        @if($sortField === $column['field'])
                                            @if($sortDirection === 'asc')
                                                <i class="ki-outline ki-up fs-6 text-primary"></i>
                                            @else
                                                <i class="ki-outline ki-down fs-6 text-primary"></i>
                                            @endif
                                        @else
                                            <i class="ki-outline ki-sort fs-6 text-gray-400"></i>
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </th>
                    @endforeach
                    @if(method_exists($this, 'actions'))
                        <th class="text-end min-w-100px pe-4">
                            <span class="text-uppercase fs-7 fw-bold">Actions</span>
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        @foreach($columns as $column)
                            <td class="ps-4">
                                <span class="text-gray-800 fw-normal fs-6">
                                    @if(isset($column['format']))
                                        {!! $column['format']($row) !!}
                                    @else
                                        {{ data_get($row, $column['field']) }}
                                    @endif
                                </span>
                            </td>
                        @endforeach
                        @if(method_exists($this, 'actions'))
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    {!! $this->actions($row) !!}
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) + (method_exists($this, 'actions') ? 1 : 0) }}" class="text-center py-10">
                            <div class="d-flex flex-column align-items-center py-10">
                                <i class="ki-outline ki-file-sheet fs-3x text-gray-400 mb-4"></i>
                                <span class="text-gray-600 fs-4 fw-semibold">No results found</span>
                                @if($search)
                                    <span class="text-gray-500 fs-6 mt-2">Try adjusting your search</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footer con paginación --}}
    <div class="d-flex flex-stack flex-wrap pt-5 border-t border-gray-200">
        <div class="text-gray-600 fs-6 fw-semibold">
            Showing
            <span class="fw-bold text-gray-800">{{ $rows->firstItem() ?? 0 }}</span>
            to
            <span class="fw-bold text-gray-800">{{ $rows->lastItem() ?? 0 }}</span>
            of
            <span class="fw-bold text-gray-800">{{ $rows->total() }}</span>
            results
        </div>
        <div class="d-flex align-items-center">
            {{ $rows->links() }}
        </div>
    </div>
</div>
