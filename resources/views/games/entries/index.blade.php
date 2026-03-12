@extends('layouts.metronic.app')

@section('title', __('Game Entries'))
@section('page_title', __('Game Entries'))

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
                <form id="entrySearchForm" method="GET" action="{{ route('games.entries.index') }}">
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
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
                            placeholder="{{ __('Search entry') }}"
                            value="{{ $search }}"
                            data-entry-filter="search"
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
                    <form class="px-7 py-5" method="GET" action="{{ route('games.entries.index') }}">
                        <div class="fs-5 text-gray-900 fw-bold mb-5">{{ __('Filter options') }}</div>

                        <div class="mb-8">
                            <label class="form-label fw-semibold">{{ __('Status') }}</label>
                            <select name="status" class="form-select form-select-solid">
                                <option value="">{{ __('Any status') }}</option>
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

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
                            <a href="{{ route('games.entries.index') }}" class="btn btn-light btn-sm">{{ __('Reset') }}</a>
                            <button type="submit" class="btn btn-primary btn-sm">{{ __('Apply') }}</button>
                        </div>
                    </form>
                </div>

                @can('games.edit.content')
                    <a class="btn btn-primary" href="{{ route('games.entries.create') }}">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        {{ __('Add entry') }}
                    </a>
                @endcan

                @can('winners.view.entity')
                    <a class="btn btn-light-primary" href="{{ route('games.winners.index', array_filter(['game_id' => $gameFilter > 0 ? $gameFilter : null])) }}">
                        <i class="ki-duotone ki-award fs-2"></i>
                        {{ __('Winners') }}
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
                        <th class="min-w-210px">{{ __('Participant') }}</th>
                        <th class="min-w-100px">{{ __('Status') }}</th>
                        <th class="min-w-130px">{{ __('Winner') }}</th>
                        <th class="min-w-90px">{{ __('Score') }}</th>
                        <th class="min-w-150px">{{ __('Submitted at') }}</th>
                        <th class="text-end min-w-90px">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 fw-semibold">
                    @forelse ($entries as $entry)
                        @php
                            $participantLabel = $entry->participant_name
                                ?? $entry->participant_email
                                ?? $entry->participantUser?->name
                                ?? ('#'.$entry->id);
                        @endphp
                        <tr>
                            <td>#{{ $entry->id }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-900 fw-bold">{{ $entry->game?->name ?? __('Unknown game') }}</span>
                                    <span class="text-muted fs-7">{{ $entry->game?->slug }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold">{{ $participantLabel }}</span>
                                    @if ($entry->participant_email || $entry->participantUser?->email)
                                        <span class="text-muted fs-7">{{ $entry->participant_email ?? $entry->participantUser?->email }}</span>
                                    @endif
                                    @if ($entry->participant_phone)
                                        <span class="text-muted fs-7">{{ $entry->participant_phone }}</span>
                                    @endif
                                </div>
                            </td>
                            <td><span class="badge badge-light">{{ $statusOptions[$entry->status] ?? __('Unknown') }}</span></td>
                            <td>
                                @if ($entry->winner_id)
                                    <div class="d-flex flex-column">
                                        <span class="badge badge-light-success">{{ __('Winner') }} #{{ $entry->winner_position }}</span>
                                        @if ($entry->winner_prize_name)
                                            <span class="text-muted fs-7 mt-1">{{ $entry->winner_prize_name }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="badge badge-light">{{ __('No') }}</span>
                                @endif
                            </td>
                            <td>{{ $entry->score !== null ? number_format((float) $entry->score, 2) : '-' }}</td>
                            <td>{{ $entry->submitted_at ? $entry->submitted_at->format('d/m/Y H:i') : '-' }}</td>
                            <td class="text-end">
                                @can('games.edit.content')
                                    <a class="btn btn-sm btn-light-primary" href="{{ route('games.entries.edit', ['entry' => $entry]) }}">{{ __('Edit') }}</a>

                                    <form class="d-inline" method="POST" action="{{ route('games.entries.destroy', ['entry' => $entry]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-light-danger"
                                            onclick="return confirm('{{ __('Are you sure you want to delete this entry?') }}')"
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
                            <td colspan="8" class="text-center text-muted py-10">{{ __('No entries found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($entries->total() > 0)
                <div class="d-flex flex-stack flex-wrap pt-5">
                    <div class="fs-6 fw-semibold text-gray-700">
                        {{ __('Showing :from to :to of :total records', ['from' => $entries->firstItem(), 'to' => $entries->lastItem(), 'total' => $entries->total()]) }}
                    </div>

                    <ul class="pagination">
                        <li class="page-item previous {{ $entries->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $entries->previousPageUrl() ?: '#' }}"><i class="previous"></i></a>
                        </li>

                        @foreach ($entries->getUrlRange(1, $entries->lastPage()) as $page => $url)
                            <li class="page-item {{ $page === $entries->currentPage() ? 'active' : '' }}">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endforeach

                        <li class="page-item next {{ $entries->hasMorePages() ? '' : 'disabled' }}">
                            <a class="page-link" href="{{ $entries->nextPageUrl() ?: '#' }}"><i class="next"></i></a>
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
            const searchInput = document.querySelector('[data-entry-filter="search"]');
            const searchForm = document.getElementById('entrySearchForm');

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
