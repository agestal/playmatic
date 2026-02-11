@extends('layouts.metronic.app')

@section('title', __('Games'))
@section('page_title', __('Games'))

@section('content')
    @if (session('status'))
        <div class="alert alert-success mb-6">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-6">{{ $errors->first() }}</div>
    @endif

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <form id="gamesSearchForm" method="GET" action="{{ route('games.index') }}">
                    <input type="hidden" name="type" value="{{ $typeFilter }}">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">

                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <input
                            type="text"
                            name="search"
                            class="form-control form-control-solid w-250px ps-13"
                            placeholder="{{ __('Search game') }}"
                            value="{{ $search }}"
                            data-games-filter="search"
                        >
                    </div>
                </form>
            </div>

            <div class="card-toolbar d-flex gap-3">
                <button
                    type="button"
                    class="btn btn-light-primary"
                    data-kt-menu-trigger="click"
                    data-kt-menu-placement="bottom-end"
                >
                    <i class="ki-duotone ki-filter fs-2"></i>
                    {{ __('Filter') }}
                </button>

                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                    <form class="px-7 py-5" method="GET" action="{{ route('games.index') }}">
                        <div class="fs-5 text-gray-900 fw-bold mb-5">{{ __('Filter options') }}</div>

                        <div class="mb-8">
                            <label class="form-label fw-semibold">{{ __('Type') }}</label>
                            <select name="type" class="form-select form-select-solid">
                                <option value="">{{ __('Any type') }}</option>
                                @foreach ($typeOptions as $typeName)
                                    <option value="{{ $typeName }}" @selected($typeFilter === $typeName)>{{ $typeName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-8">
                            <label class="form-label fw-semibold">{{ __('Rows per page') }}</label>
                            <select name="per_page" class="form-select form-select-solid">
                                @foreach ([10, 25, 50, 100] as $option)
                                    <option value="{{ $option }}" @selected((int) $perPage === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>

                        <input type="hidden" name="search" value="{{ $search }}">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('games.index') }}" class="btn btn-light btn-sm">{{ __('Reset') }}</a>
                            <button type="submit" class="btn btn-primary btn-sm">{{ __('Apply') }}</button>
                        </div>
                    </form>
                </div>

                @can('games.edit.entity')
                    <a class="btn btn-primary" href="{{ route('games.create') }}">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        {{ __('Add game') }}
                    </a>
                @endcan
            </div>
        </div>

        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-200px">{{ __('Game') }}</th>
                        <th class="min-w-140px">{{ __('Type') }}</th>
                        <th class="min-w-120px">{{ __('Entries') }}</th>
                        <th class="min-w-120px">{{ __('Winners') }}</th>
                        <th class="min-w-110px">{{ __('Status') }}</th>
                        <th class="text-end min-w-90px">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 fw-semibold">
                    @forelse ($games as $game)
                        <tr>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-900 fw-bold">{{ $game->name }}</span>
                                    <span class="text-muted fs-7">{{ $game->slug }}</span>
                                </div>
                            </td>
                            <td>{{ $game->game_type }}</td>
                            <td><span class="badge badge-light">{{ $game->entries_count }}</span></td>
                            <td><span class="badge badge-light-info">{{ $game->winners_count }}</span></td>
                            <td>
                                @if ($game->is_active)
                                    <span class="badge badge-light-success">{{ __('Active') }}</span>
                                @else
                                    <span class="badge badge-light-danger">{{ __('Inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @can('games.edit.entity')
                                    <a class="btn btn-sm btn-light-primary" href="{{ route('games.edit', ['game' => $game]) }}">{{ __('Edit') }}</a>

                                    <form class="d-inline" method="POST" action="{{ route('games.destroy', ['game' => $game]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-light-danger"
                                            onclick="return confirm('{{ __('Are you sure you want to remove this game?') }}')"
                                        >
                                            {{ __('Delete') }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">-</span>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-10">{{ __('No games found for this tenant.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($games->total() > 0)
                <div class="d-flex flex-stack flex-wrap pt-5">
                    <div class="fs-6 fw-semibold text-gray-700">
                        {{ __('Showing :from to :to of :total records', ['from' => $games->firstItem(), 'to' => $games->lastItem(), 'total' => $games->total()]) }}
                    </div>

                    <ul class="pagination">
                        <li class="page-item previous {{ $games->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $games->previousPageUrl() ?: '#' }}"><i class="previous"></i></a>
                        </li>

                        @foreach ($games->getUrlRange(1, $games->lastPage()) as $page => $url)
                            <li class="page-item {{ $page === $games->currentPage() ? 'active' : '' }}">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endforeach

                        <li class="page-item next {{ $games->hasMorePages() ? '' : 'disabled' }}">
                            <a class="page-link" href="{{ $games->nextPageUrl() ?: '#' }}"><i class="next"></i></a>
                        </li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const searchInput = document.querySelector('[data-games-filter="search"]');
            const searchForm = document.getElementById('gamesSearchForm');

            if (!searchInput || !searchForm) {
                return;
            }

            let timer = null;

            searchInput.addEventListener('keyup', function () {
                if (timer) {
                    clearTimeout(timer);
                }

                timer = setTimeout(function () {
                    searchForm.submit();
                }, 450);
            });
        })();
    </script>
@endpush
