@php
    $recordCount = data_get($setUp, 'footer.recordCount', 'full');
    $first = method_exists($this->records, 'firstItem') ? ($this->records->firstItem() ?? 0) : 0;
    $last = method_exists($this->records, 'lastItem') ? ($this->records->lastItem() ?? 0) : 0;
    $total = method_exists($this->records, 'total') ? $this->records->total() : 0;
@endphp

<div class="border-t border-gray-200 bg-gray-50/50 px-5 py-3">
    @includeIf(data_get($setUp, 'footer.includeViewOnTop'))

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-wrap items-center gap-4">
            @if (filled(data_get($setUp, 'footer.perPage')) && count(data_get($setUp, 'footer.perPageValues', [])) > 1)
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600">{{ trans('livewire-powergrid::datatable.labels.results_per_page') }}</span>
                    <select
                        wire:model.live="setUp.footer.perPage"
                        class="{{ theme_style($theme, 'footer.select') }}"
                    >
                        @foreach (data_get($setUp, 'footer.perPageValues') as $value)
                            <option value="{{ $value }}">
                                @if ($value == 0)
                                    {{ trans('livewire-powergrid::datatable.labels.all') }}
                                @else
                                    {{ $value }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if ($recordCount === 'full')
                <p class="text-sm text-gray-600">
                    {{ trans('livewire-powergrid::datatable.pagination.showing') }}
                    <span class="font-semibold text-gray-900">{{ $first }}</span>
                    {{ trans('livewire-powergrid::datatable.pagination.to') }}
                    <span class="font-semibold text-gray-900">{{ $last }}</span>
                    {{ trans('livewire-powergrid::datatable.pagination.of') }}
                    <span class="font-semibold text-gray-900">{{ $total }}</span>
                    {{ trans('livewire-powergrid::datatable.pagination.results') }}
                </p>
            @elseif ($recordCount === 'short')
                <p class="text-sm text-gray-600">
                    <span class="font-semibold text-gray-900">{{ $first }}</span>
                    -
                    <span class="font-semibold text-gray-900">{{ $last }}</span>
                    /
                    <span class="font-semibold text-gray-900">{{ $total }}</span>
                </p>
            @elseif ($recordCount === 'min')
                <p class="text-sm text-gray-600">
                    <span class="font-semibold text-gray-900">{{ $first }}</span>
                    -
                    <span class="font-semibold text-gray-900">{{ $last }}</span>
                </p>
            @endif
        </div>

        <div class="flex justify-end">
            @if (method_exists($this->records, 'links'))
                {!! $this->records->links(data_get($theme, 'layout.pagination'), [
                    'recordCount' => $recordCount,
                    'perPage' => data_get($setUp, 'footer.perPage'),
                    'perPageValues' => data_get($setUp, 'footer.perPageValues'),
                ]) !!}
            @endif
        </div>
    </div>

    @includeIf(data_get($setUp, 'footer.includeViewOnBottom'))
</div>
