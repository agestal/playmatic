@extends('layouts.metronic.app')

@section('title', __('Attendance Guess Rounds'))
@section('page_title', __('Attendance Guess Rounds'))

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
                <form method="GET" action="{{ route('games.attendance-rounds.index') }}" class="d-flex gap-3 align-items-center flex-wrap">
                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <input
                            type="text"
                            name="search"
                            class="form-control form-control-solid w-250px ps-13"
                            placeholder="{{ __('Search round') }}"
                            value="{{ $search }}"
                        >
                    </div>

                    <select name="per_page" class="form-select form-select-solid w-120px">
                        @foreach ([10, 25, 50, 100] as $option)
                            <option value="{{ $option }}" @selected((int) $perPage === $option)>{{ $option }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-light-primary">{{ __('Apply') }}</button>
                    <a href="{{ route('games.attendance-rounds.index') }}" class="btn btn-light">{{ __('Reset') }}</a>
                </form>
            </div>

            <div class="card-toolbar">
                @can('games.edit.content')
                    <a class="btn btn-primary" href="{{ route('games.attendance-rounds.create') }}">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        {{ __('New round') }}
                    </a>
                @endcan
            </div>
        </div>

        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-60px">#</th>
                        <th class="min-w-220px">{{ __('Round') }}</th>
                        <th class="min-w-130px">{{ __('Mode') }}</th>
                        <th class="min-w-220px">{{ __('Window') }}</th>
                        <th class="min-w-90px">{{ __('Entries') }}</th>
                        <th class="min-w-90px">{{ __('Winners') }}</th>
                        <th class="min-w-100px">{{ __('Result') }}</th>
                        <th class="text-end min-w-220px">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 fw-semibold">
                    @php $now = now(); @endphp
                    @forelse ($rounds as $round)
                        @php
                            $isActive = $round->isActiveAt($now);
                            $canDeactivate = $isActive || ($round->management_mode === 'scheduled' && $round->deactivated_at === null);
                        @endphp
                        <tr>
                            <td>#{{ $round->id }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-900 fw-bold">{{ $round->name }}</span>
                                    <span class="text-muted fs-7">{{ __('Created') }}: {{ $round->created_at?->format('d/m/Y H:i') }}</span>
                                </div>
                            </td>
                            <td>
                                @if ($round->management_mode === 'manual')
                                    <span class="badge badge-light-primary">{{ __('Manual') }}</span>
                                @else
                                    <span class="badge badge-light-info">{{ __('Scheduled') }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($round->management_mode === 'scheduled')
                                    <div class="d-flex flex-column">
                                        <span>{{ $round->starts_at?->format('d/m/Y H:i') ?: '-' }}</span>
                                        <span class="text-muted fs-7">{{ $round->ends_at?->format('d/m/Y H:i') ?: '-' }}</span>
                                    </div>
                                @else
                                    <div class="d-flex flex-column">
                                        <span>{{ __('Activated') }}: {{ $round->activated_at?->format('d/m/Y H:i') ?: '-' }}</span>
                                        <span class="text-muted fs-7">{{ __('Deactivated') }}: {{ $round->deactivated_at?->format('d/m/Y H:i') ?: '-' }}</span>
                                    </div>
                                @endif
                            </td>
                            <td><span class="badge badge-light">{{ $round->entries_count }}</span></td>
                            <td><span class="badge badge-light-success">{{ $round->winners_count }}</span></td>
                            <td>
                                {{ $round->result_value !== null ? number_format((float) $round->result_value, 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-end">
                                @can('games.edit.content')
                                    <a class="btn btn-sm btn-light-primary" href="{{ route('games.attendance-rounds.edit', ['round' => $round]) }}">{{ __('Edit') }}</a>
                                    <a class="btn btn-sm btn-light-info" href="{{ route('games.attendance-rounds.export', ['round' => $round]) }}">{{ __('Export') }}</a>
                                    <a
                                        class="btn btn-sm btn-light-success"
                                        href="{{ route('games.winners.create', ['game_id' => $round->game_id, 'game_round_id' => $round->id]) }}"
                                    >
                                        {{ __('Create winner') }}
                                    </a>

                                    @if ($round->management_mode === 'manual' && ! $isActive)
                                        <form class="d-inline" method="POST" action="{{ route('games.attendance-rounds.activate', ['round' => $round]) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light-success">{{ __('Activate') }}</button>
                                        </form>
                                    @endif

                                    @if ($canDeactivate)
                                        <form class="d-inline" method="POST" action="{{ route('games.attendance-rounds.deactivate', ['round' => $round]) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light-warning">{{ __('Deactivate') }}</button>
                                        </form>
                                    @endif

                                    @if ($round->result_value !== null && $round->entries_count > 0)
                                        <form class="d-inline" method="POST" action="{{ route('games.attendance-rounds.generate-winners', ['round' => $round]) }}">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-light-info"
                                                onclick="return confirm('{{ __('Generate winners for this round? Existing round winners will be replaced.') }}')"
                                            >
                                                {{ __('Generate winners') }}
                                            </button>
                                        </form>
                                    @endif

                                    @if ($round->winners_count > 0)
                                        <form class="d-inline" method="POST" action="{{ route('games.attendance-rounds.reset-winners', ['round' => $round]) }}">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-light-dark"
                                                onclick="return confirm('{{ __('Reset winners for this round? This will remove generated winners.') }}')"
                                            >
                                                {{ __('Reset winners') }}
                                            </button>
                                        </form>
                                    @endif

                                    <form class="d-inline" method="POST" action="{{ route('games.attendance-rounds.destroy', ['round' => $round]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-light-danger"
                                            onclick="return confirm('{{ __('Are you sure you want to delete this round?') }}')"
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
                            <td colspan="8" class="text-center text-muted py-10">{{ __('No rounds found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($rounds->total() > 0)
                <div class="d-flex flex-stack flex-wrap pt-5">
                    <div class="fs-6 fw-semibold text-gray-700">
                        {{ __('Showing :from to :to of :total records', ['from' => $rounds->firstItem(), 'to' => $rounds->lastItem(), 'total' => $rounds->total()]) }}
                    </div>

                    <ul class="pagination">
                        <li class="page-item previous {{ $rounds->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $rounds->previousPageUrl() ?: '#' }}"><i class="previous"></i></a>
                        </li>

                        @foreach ($rounds->getUrlRange(1, $rounds->lastPage()) as $page => $url)
                            <li class="page-item {{ $page === $rounds->currentPage() ? 'active' : '' }}">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endforeach

                        <li class="page-item next {{ $rounds->hasMorePages() ? '' : 'disabled' }}">
                            <a class="page-link" href="{{ $rounds->nextPageUrl() ?: '#' }}"><i class="next"></i></a>
                        </li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endsection
