@if ($paginator->hasPages())
    <nav
        class="pg-pagination"
        role="navigation"
        aria-label="Pagination Navigation"
        wire:loading.class="opacity-60"
        wire:target="loadMore"
    >
        <button
            type="button"
            wire:click="previousPage('{{ $paginator->getPageName() }}')"
            @disabled($paginator->onFirstPage())
            @class([
                'pg-pagination-btn',
                'is-disabled' => $paginator->onFirstPage(),
            ])
            aria-label="Previous page"
        >
            &lsaquo;
        </button>

        <div class="pg-pagination-pages">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="pg-pagination-ellipsis">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pg-pagination-btn is-active">{{ $page }}</span>
                        @else
                            <button
                                type="button"
                                wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                class="pg-pagination-btn"
                                aria-label="Go to page {{ $page }}"
                            >
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        <button
            type="button"
            wire:click="nextPage('{{ $paginator->getPageName() }}')"
            @disabled(!$paginator->hasMorePages())
            @class([
                'pg-pagination-btn',
                'is-disabled' => !$paginator->hasMorePages(),
            ])
            aria-label="Next page"
        >
            &rsaquo;
        </button>
    </nav>
@endif
