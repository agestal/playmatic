<div class="pg-toolbar pg-toolbar--tinted border-b border-gray-200 px-5 py-4">
    @includeIf(data_get($setUp, 'header.includeViewOnTop'))

    <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
        <div class="flex flex-wrap items-center gap-2">
            <div x-data="pgRenderActions">
                <span class="pg-actions flex flex-wrap items-center gap-2" x-html="toHtml"></span>
            </div>

            @if (data_get($setUp, 'exportable'))
                <div id="pg-header-export">
                    @include('livewire-powergrid::components.frameworks.tailwind.header.export')
                </div>
            @endif

            @includeIf('livewire-powergrid::components.frameworks.tailwind.header.toggle-columns')
            @includeIf('livewire-powergrid::components.frameworks.tailwind.header.soft-deletes')

            @if (config('livewire-powergrid.filter') === 'outside' && count($this->filters()) > 0)
                @includeIf('livewire-powergrid::components.frameworks.tailwind.header.filters')
            @endif

            @includeWhen(boolval(data_get($setUp, 'header.wireLoading')),
                'livewire-powergrid::components.frameworks.tailwind.header.loading')
        </div>

        @include('powergrid.metronic.search')
    </div>

    @includeIf('livewire-powergrid::components.frameworks.tailwind.header.enabled-filters')
    @includeWhen(data_get($setUp, 'exportable.batchExport.queues', 0), 'livewire-powergrid::components.frameworks.tailwind.header.batch-exporting')
    @includeWhen($multiSort, 'livewire-powergrid::components.frameworks.tailwind.header.multi-sort')
    @includeIf(data_get($setUp, 'header.includeViewOnBottom'))
    @includeIf('livewire-powergrid::components.frameworks.tailwind.header.message-soft-deletes')
</div>
