@if (data_get($setUp, 'header.searchInput'))
    <div class="pg-search-wrap w-full sm:w-60 lg:w-64">
        <div class="group relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center ps-3">
                <i class="ki-outline ki-magnifier {{ theme_style($theme, 'searchBox.iconSearch') }}"></i>
            </span>

            <input
                wire:model.live.debounce.500ms="search"
                type="text"
                class="{{ theme_style($theme, 'searchBox.input') }}"
                placeholder="{{ trans('livewire-powergrid::datatable.placeholders.search') }}"
            >

            @if ($search)
                <button
                    type="button"
                    wire:click.prevent="$set('search', '')"
                    class="absolute inset-y-0 right-0 flex items-center pe-3"
                    aria-label="{{ __('Clear search') }}"
                >
                    <i class="ki-outline ki-cross text-xs {{ theme_style($theme, 'searchBox.iconClose') }}"></i>
                </button>
            @endif
        </div>
    </div>
@endif
