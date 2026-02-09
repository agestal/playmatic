<div>
    {{-- Toolbar superior --}}
    @if ($this->searchIsEnabled() || $this->paginationIsEnabled())
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            {{-- Búsqueda --}}
            @if ($this->searchIsEnabled() && $this->searchVisibilityIsEnabled())
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <i class="ki-filled ki-magnifier absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input
                            wire:model.live.debounce.300ms="search"
                            type="text"
                            placeholder="Search users..."
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                        @if($search)
                            <button
                                wire:click="$set('search', '')"
                                type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            >
                                <i class="ki-filled ki-cross text-sm"></i>
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Per page selector --}}
            @if($this->paginationIsEnabled())
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600">Show</span>
                    <select
                        wire:model.live="perPage"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                    >
                        @foreach($this->getPerPageAccepted() as $item)
                            <option value="{{ $item }}">{{ $item }}</option>
                        @endforeach
                    </select>
                    <span class="text-sm text-gray-600">entries</span>
                </div>
            @endif
        </div>
    @endif

    {{-- Tabla con diseño Metronic --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            @foreach($this->columns as $column)
                                @if($column->isVisible())
                                    <th
                                        class="min-w-120px ps-6 {{ $column->isSortable() ? 'cursor-pointer' : '' }}"
                                        @if($column->isSortable())
                                            wire:click="sortBy('{{ $column->getColumnSelectName() }}')"
                                        @endif
                                    >
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="text-uppercase fs-7 fw-semibold">{{ $column->getTitle() }}</span>
                                            @if($column->isSortable())
                                                <span class="svg-icon svg-icon-muted svg-icon-2">
                                                    @if($this->hasSort($column->getColumnSelectName()))
                                                        @if($this->getSort($column->getColumnSelectName()) === 'asc')
                                                            <i class="ki-filled ki-up fs-6"></i>
                                                        @else
                                                            <i class="ki-filled ki-down fs-6"></i>
                                                        @endif
                                                    @else
                                                        <i class="ki-filled ki-sort fs-6 text-gray-400"></i>
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    </th>
                                @endif
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->getRows() as $row)
                            <tr wire:key="row-{{ $row->getKey() }}">
                                @foreach($this->columns as $column)
                                    @if($column->isVisible())
                                        <td class="text-gray-800 fw-normal ps-6">
                                            {!! $column->renderContents($row) !!}
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($this->columns) }}" class="text-center py-12">
                                    <div class="d-flex flex-column align-items-center gap-3">
                                        <i class="ki-filled ki-file-sheet fs-3x text-gray-300"></i>
                                        <p class="text-gray-600 fs-5 mb-0">No users found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Footer con paginación --}}
        @if ($this->paginationIsEnabled() && $this->getRows()->hasPages())
            <div class="card-footer d-flex align-items-center justify-content-between py-4">
                <div class="text-gray-600 fs-7">
                    Showing <span class="fw-semibold">{{ $this->getRows()->firstItem() ?? 0 }}</span> to
                    <span class="fw-semibold">{{ $this->getRows()->lastItem() ?? 0 }}</span> of
                    <span class="fw-semibold">{{ $this->getRows()->total() }}</span> results
                </div>
                <div>
                    {{ $this->getRows()->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
