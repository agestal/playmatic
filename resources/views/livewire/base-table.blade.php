<div>
    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-4 pb-5 mb-5">
        {{-- Búsqueda --}}
        <div class="relative flex-1 max-w-md">
            <i class="ki-outline ki-magnifier absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-base"></i>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                class="kt-input ps-10 w-full"
                placeholder="Search users..."
            >
            @if($search)
                <button
                    wire:click="$set('search', '')"
                    type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2"
                >
                    <i class="ki-outline ki-cross text-sm text-gray-500 hover:text-gray-700"></i>
                </button>
            @endif
        </div>

        {{-- Per page selector --}}
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-600 font-medium">Show</span>
            <select wire:model.live="perPage" class="kt-input kt-input-sm w-20">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span class="text-sm text-gray-600 font-medium">entries</span>
        </div>
    </div>

    {{-- Tabla con clases Metronic nativas --}}
    <div class="kt-table-wrapper">
        <table class="kt-table">
            <thead>
                <tr>
                    @foreach($columns as $column)
                        <th class="{{ $column['sortable'] ?? false ? 'cursor-pointer' : '' }}"
                            @if($column['sortable'] ?? false)
                                wire:click="sortBy('{{ $column['field'] }}')"
                            @endif
                        >
                            <div class="flex items-center gap-2">
                                <span>{{ $column['label'] }}</span>
                                @if($column['sortable'] ?? false)
                                    @if($sortField === $column['field'])
                                        @if($sortDirection === 'asc')
                                            <i class="ki-outline ki-up text-xs text-primary"></i>
                                        @else
                                            <i class="ki-outline ki-down text-xs text-primary"></i>
                                        @endif
                                    @else
                                        <i class="ki-outline ki-sort text-xs text-gray-400"></i>
                                    @endif
                                @endif
                            </div>
                        </th>
                    @endforeach
                    @if(method_exists($this, 'actions'))
                        <th class="text-end">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        @foreach($columns as $column)
                            <td>
                                @if(isset($column['format']))
                                    {!! $column['format']($row) !!}
                                @else
                                    <span class="text-sm text-gray-800">{{ data_get($row, $column['field']) }}</span>
                                @endif
                            </td>
                        @endforeach
                        @if(method_exists($this, 'actions'))
                            <td class="text-end">
                                <div class="flex items-center justify-end gap-1">
                                    {!! $this->actions($row) !!}
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) + (method_exists($this, 'actions') ? 1 : 0) }}" class="text-center py-10">
                            <div class="flex flex-col items-center gap-3 py-8">
                                <i class="ki-outline ki-file-sheet text-5xl text-gray-300"></i>
                                <span class="text-gray-600 font-medium">No results found</span>
                                @if($search)
                                    <span class="text-gray-500 text-sm">Try adjusting your search</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footer con paginación --}}
    <div class="flex items-center justify-between mt-5 pt-5 border-t border-gray-200">
        <div class="text-sm text-gray-600">
            Showing
            <span class="font-semibold text-gray-900">{{ $rows->firstItem() ?? 0 }}</span>
            to
            <span class="font-semibold text-gray-900">{{ $rows->lastItem() ?? 0 }}</span>
            of
            <span class="font-semibold text-gray-900">{{ $rows->total() }}</span>
            results
        </div>
        <div>
            {{ $rows->links() }}
        </div>
    </div>
    <style>
    .kt-table-wrapper {
        background: white;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }

    .kt-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .kt-table thead th {
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        padding: 1rem 1.5rem;
        text-align: left;
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .kt-table tbody td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f3f4f6;
        color: #1f2937;
        font-size: 0.875rem;
    }

    .kt-table tbody tr:last-child td {
        border-bottom: none;
    }

    .kt-table tbody tr:hover {
        background: #f9fafb;
    }

    .kt-badge {
        display: inline-flex;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .kt-badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .kt-btn-icon {
        width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.375rem;
        transition: all 0.15s;
    }

    .kt-btn-light {
        background: #f3f4f6;
        color: #4b5563;
    }

    .kt-btn-light:hover {
        background: #e5e7eb;
    }

    .kt-btn-light-danger {
        background: #fef2f2;
        color: #dc2626;
    }

    .kt-btn-light-danger:hover {
        background: #fee2e2;
    }
    /* Input de búsqueda */
.kt-input {
    width: 100%;
    padding: 0.625rem 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: all 0.15s;
    background: white;
}

.kt-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.kt-input::placeholder {
    color: #9ca3af;
}

/* Select */
.kt-input-sm {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

/* Espaciado del toolbar */
.flex.flex-wrap.items-center.justify-between.gap-4.pb-5.mb-5 {
    padding-bottom: 1.25rem;
    margin-bottom: 1.25rem;
    border-bottom: 1px solid #e5e7eb;
}

/* Footer paginación */
.flex.items-center.justify-between.mt-5.pt-5.border-t.border-gray-200 {
    margin-top: 1.25rem;
    padding-top: 1.25rem;
    border-top: 1px solid #e5e7eb;
}

/* Iconos */
.ki-outline {
    font-size: 1rem;
}
</style>
</div>
