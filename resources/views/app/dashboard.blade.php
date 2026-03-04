@extends('layouts.metronic.app')

@section('title', __('Dashboard'))
@section('page_title', __('Games Dashboard'))
@section('page_description', __('Cross-game operational overview for the active tenant.'))

@push('styles')
    <style>
        .pm-dashboard-hero {
            position: relative;
            overflow: hidden;
            border: 0;
            border-radius: 1rem;
            background:
                radial-gradient(circle at 10% 18%, rgba(var(--pm-primary-rgb), 0.32) 0%, rgba(var(--pm-primary-rgb), 0) 48%),
                radial-gradient(circle at 90% 18%, rgba(var(--pm-secondary-rgb), 0.28) 0%, rgba(var(--pm-secondary-rgb), 0) 46%),
                linear-gradient(120deg, var(--pm-primary) 0%, var(--pm-gradient-end) 100%);
            box-shadow: 0 20px 45px rgba(var(--pm-primary-rgb), 0.25);
        }

        .pm-dashboard-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(130deg, rgba(255, 255, 255, 0.20) 0%, rgba(255, 255, 255, 0.05) 38%, rgba(12, 17, 29, 0.20) 100%);
            pointer-events: none;
        }

        .pm-dashboard-hero .card-body {
            position: relative;
            z-index: 1;
        }

        .pm-kpi-card {
            border: 1px solid rgba(var(--pm-primary-rgb), 0.14);
            border-radius: 0.95rem;
            background: linear-gradient(155deg, rgba(var(--pm-primary-rgb), 0.10) 0%, rgba(255, 255, 255, 0.95) 44%, rgba(var(--pm-secondary-rgb), 0.10) 100%);
            box-shadow: 0 12px 28px rgba(var(--pm-primary-rgb), 0.10);
        }

        .pm-kpi-icon {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            color: var(--pm-primary);
            background: rgba(var(--pm-primary-rgb), 0.16);
        }

        .pm-table-card {
            border: 1px solid rgba(var(--pm-primary-rgb), 0.13);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 14px 32px rgba(var(--pm-primary-rgb), 0.10);
        }

        .pm-table-card .card-header {
            background: linear-gradient(130deg, rgba(var(--pm-primary-rgb), 0.16) 0%, rgba(var(--pm-secondary-rgb), 0.14) 100%);
        }
    </style>
@endpush

@section('content')
    @php
        $tenantName = $tenant?->name ?? __('Global view');
    @endphp

    <div class="card pm-dashboard-hero mb-8">
        <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4 py-8">
            <div>
                <span class="badge fw-semibold mb-3" style="background-color: rgba(255, 255, 255, 0.22); color: #ffffff;">{{ __('Statistics') }}</span>
                <h2 class="fw-bolder text-white mb-1">{{ __('Games operational summary') }}</h2>
                <div class="text-white opacity-75">{{ __('Current context') }}: {{ $tenantName }}</div>
            </div>
            <div class="d-inline-flex align-items-center gap-3 p-3 rounded-3" style="background-color: rgba(255, 255, 255, 0.12);">
                <span class="symbol symbol-40px">
                    <span class="symbol-label text-white" style="background-color: rgba(255, 255, 255, 0.24);">
                        <i class="ki-duotone ki-abstract-41 fs-2 text-white">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                    </span>
                </span>
                <div>
                    <div class="text-white fw-semibold fs-7 text-uppercase">{{ __('Brand performance') }}</div>
                    <div class="text-white fw-bold fs-4">{{ number_format((int) ($totals['total_plays'] ?? 0)) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-6 mb-8">
        <div class="col-sm-6 col-xl-3">
            <div class="card pm-kpi-card h-100">
                <div class="card-body d-flex flex-column">
                    <span class="pm-kpi-icon mb-4"><i class="ki-duotone ki-element-11"></i></span>
                    <div class="text-muted fw-semibold mb-2">{{ __('Active games') }}</div>
                    <div class="fs-2hx fw-bolder text-gray-900">{{ number_format((int) ($totals['active_games'] ?? 0)) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card pm-kpi-card h-100">
                <div class="card-body d-flex flex-column">
                    <span class="pm-kpi-icon mb-4"><i class="ki-duotone ki-calendar"></i></span>
                    <div class="text-muted fw-semibold mb-2">{{ __('Total plays') }}</div>
                    <div class="fs-2hx fw-bolder text-gray-900">{{ number_format((int) ($totals['total_plays'] ?? 0)) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card pm-kpi-card h-100">
                <div class="card-body d-flex flex-column">
                    <span class="pm-kpi-icon mb-4"><i class="ki-duotone ki-shield-tick"></i></span>
                    <div class="text-muted fw-semibold mb-2">{{ __('Total participants') }}</div>
                    <div class="fs-2hx fw-bolder text-gray-900">{{ number_format((int) ($totals['total_participants'] ?? 0)) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card pm-kpi-card h-100">
                <div class="card-body d-flex flex-column">
                    <span class="pm-kpi-icon mb-4"><i class="ki-duotone ki-crown"></i></span>
                    <div class="text-muted fw-semibold mb-2">{{ __('Total winners') }}</div>
                    <div class="fs-2hx fw-bolder text-gray-900">{{ number_format((int) ($totals['total_winners'] ?? 0)) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-6">
        <div class="col-12 col-xl-4">
            <div class="card h-100 border-0" style="border-radius: 1rem; background: linear-gradient(135deg, rgba(var(--pm-primary-rgb), 0.92) 0%, rgba(var(--pm-secondary-rgb), 0.88) 100%); box-shadow: 0 18px 36px rgba(var(--pm-primary-rgb), 0.25);">
                <div class="card-body">
                    <div class="text-white fw-semibold mb-2">{{ __('Open rounds') }}</div>
                    <div class="fs-1 fw-bolder text-white mb-3">{{ number_format((int) ($totals['open_rounds'] ?? 0)) }}</div>
                    <div class="text-white opacity-75 fs-7">{{ __('Open rounds across all assigned games.') }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="card pm-table-card h-100">
                <div class="card-header border-0">
                    <h3 class="card-title fw-bold text-gray-900">{{ __('Plays by game') }}</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-4">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>{{ __('Game') }}</th>
                                    <th>{{ __('Plays') }}</th>
                                    <th>{{ __('Rounds') }}</th>
                                    <th>{{ __('Participants') }}</th>
                                    <th>{{ __('Winners') }}</th>
                                    <th>{{ __('Open rounds') }}</th>
                                </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-700">
                                @forelse ($gameStats as $gameStat)
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-900 fw-bold">{{ $gameStat['name'] }}</span>
                                                <span class="text-muted fs-7">{{ $gameStat['slug'] }}</span>
                                            </div>
                                        </td>
                                        <td>{{ number_format((int) $gameStat['plays_count']) }}</td>
                                        <td>{{ number_format((int) $gameStat['rounds_count']) }}</td>
                                        <td>{{ number_format((int) $gameStat['entries_count']) }}</td>
                                        <td>{{ number_format((int) $gameStat['winners_count']) }}</td>
                                        <td>
                                            @if ((int) $gameStat['open_rounds_count'] > 0)
                                                <span class="badge badge-light-success">{{ number_format((int) $gameStat['open_rounds_count']) }}</span>
                                            @else
                                                <span class="badge badge-light">{{ number_format((int) $gameStat['open_rounds_count']) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-8">{{ __('No games found for this tenant.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="text-muted fs-7 mt-4">
                        {{ __('Plays are calculated from rounds when available, and fallback to entries for games without rounds.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
