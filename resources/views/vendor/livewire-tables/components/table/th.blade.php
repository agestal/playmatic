<th
    class="px-6 py-3 text-start text-xs font-semibold text-secondary-foreground uppercase tracking-wider {{ $column->isSortable() ? 'cursor-pointer select-none' : '' }}"
    @if($column->isSortable())
        wire:click="sortBy('{{ $column->getColumnSelectName() }}')"
    @endif
>
    <div class="flex items-center gap-2">
        <span>{{ $column->getTitle() }}</span>

        @if($column->isSortable())
            <span class="text-muted-foreground">
                @if($this->hasSort($column->getColumnSelectName()))
                    @if($this->getSort($column->getColumnSelectName()) === 'asc')
                        <i class="ki-filled ki-up text-xs"></i>
                    @else
                        <i class="ki-filled ki-down text-xs"></i>
                    @endif
                @else
                    <i class="ki-filled ki-sort text-xs"></i>
                @endif
            </span>
        @endif
    </div>
</th>
