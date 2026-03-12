@extends('layouts.metronic.app')

@section('title', __('Game Winners'))
@section('page_title', __('Game Winners'))

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
                <form id="winnerSearchForm" method="GET" action="{{ route('games.winners.index') }}">
                    <input type="hidden" name="game_id" value="{{ $gameFilter > 0 ? $gameFilter : '' }}">
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
                            placeholder="{{ __('Search winner') }}"
                            value="{{ $search }}"
                            data-winner-filter="search"
                        >
                    </div>
                </form>
            </div>

            <div class="card-toolbar d-flex gap-3">
                @can('participants.view.entity')
                    <a class="btn btn-light-primary" href="{{ route('games.entries.index', array_filter(['game_id' => $gameFilter > 0 ? $gameFilter : null])) }}">
                        <i class="ki-duotone ki-left fs-2"></i>
                        {{ __('Back') }}
                    </a>
                @endcan

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
                    <form class="px-7 py-5" method="GET" action="{{ route('games.winners.index') }}">
                        <div class="fs-5 text-gray-900 fw-bold mb-5">{{ __('Filter options') }}</div>

                        <div class="mb-8">
                            <label class="form-label fw-semibold">{{ __('Game') }}</label>
                            <select name="game_id" class="form-select form-select-solid">
                                <option value="">{{ __('Any game') }}</option>
                                @foreach ($gameOptions as $option)
                                    <option value="{{ $option['id'] }}" @selected((int) $gameFilter === (int) $option['id'])>{{ $option['name'] }}</option>
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
                            <a href="{{ route('games.winners.index') }}" class="btn btn-light btn-sm">{{ __('Reset') }}</a>
                            <button type="submit" class="btn btn-primary btn-sm">{{ __('Apply') }}</button>
                        </div>
                    </form>
                </div>

                @can('games.edit.content')
                    <a class="btn btn-primary" href="{{ route('games.winners.create') }}">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        {{ __('Add winner') }}
                    </a>
                @endcan
            </div>
        </div>

        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-60px">#</th>
                        <th class="min-w-160px">{{ __('Game') }}</th>
                        <th class="min-w-220px">{{ __('Winner') }}</th>
                        <th class="min-w-90px">{{ __('Position') }}</th>
                        <th class="min-w-170px">{{ __('Prize') }}</th>
                        <th class="min-w-150px">{{ __('Decided at') }}</th>
                        <th class="text-end min-w-90px">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 fw-semibold">
                    @forelse ($winners as $winner)
                        @php
                            $winnerLabel = $winner->participant_name
                                ?? $winner->participant_email
                                ?? $winner->participantUser?->name
                                ?? __('Unknown');
                        @endphp
                        <tr>
                            <td>#{{ $winner->id }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-900 fw-bold">{{ $winner->game?->name ?? __('Unknown game') }}</span>
                                    <span class="text-muted fs-7">{{ $winner->game?->slug }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold">{{ $winnerLabel }}</span>
                                    @if ($winner->participant_email || $winner->participantUser?->email)
                                        <span class="text-muted fs-7">{{ $winner->participant_email ?? $winner->participantUser?->email }}</span>
                                    @endif
                                </div>
                            </td>
                            <td><span class="badge badge-light-warning">{{ $winner->position }}</span></td>
                            <td>
                                @if ($winner->prize_name || $winner->prize_value)
                                    <div class="d-flex flex-column">
                                        <span>{{ $winner->prize_name ?: '-' }}</span>
                                        <span class="text-muted fs-7">{{ $winner->prize_value ?: '-' }}</span>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $winner->decided_at ? $winner->decided_at->format('d/m/Y H:i') : '-' }}</td>
                            <td class="text-end">
                                @can('games.edit.content')
                                    <a class="btn btn-sm btn-light-primary" href="{{ route('games.winners.edit', ['winner' => $winner]) }}">{{ __('Edit') }}</a>

                                    <form class="d-inline" method="POST" action="{{ route('games.winners.destroy', ['winner' => $winner]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-light-danger"
                                            onclick="return confirm('{{ __('Are you sure you want to delete this winner?') }}')"
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
                            <td colspan="7" class="text-center text-muted py-10">{{ __('No winners found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($winners->total() > 0)
                <div class="d-flex flex-stack flex-wrap pt-5">
                    <div class="fs-6 fw-semibold text-gray-700">
                        {{ __('Showing :from to :to of :total records', ['from' => $winners->firstItem(), 'to' => $winners->lastItem(), 'total' => $winners->total()]) }}
                    </div>

                    <ul class="pagination">
                        <li class="page-item previous {{ $winners->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $winners->previousPageUrl() ?: '#' }}"><i class="previous"></i></a>
                        </li>

                        @foreach ($winners->getUrlRange(1, $winners->lastPage()) as $page => $url)
                            <li class="page-item {{ $page === $winners->currentPage() ? 'active' : '' }}">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endforeach

                        <li class="page-item next {{ $winners->hasMorePages() ? '' : 'disabled' }}">
                            <a class="page-link" href="{{ $winners->nextPageUrl() ?: '#' }}"><i class="next"></i></a>
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
            const searchInput = document.querySelector('[data-winner-filter="search"]');
            const searchForm = document.getElementById('winnerSearchForm');

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
