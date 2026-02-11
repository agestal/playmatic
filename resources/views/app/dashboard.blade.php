@extends('layouts.metronic.app')

@section('title', __('Dashboard'))
@section('page_title', __('Executive Dashboard'))
@section('page_description', __('Real-time overview of users and activity for your business.'))

@push('styles')
    <style>
        :root {
            --dashboard-primary: #0d6efd;
            --dashboard-primary-soft: #e8f1ff;
            --dashboard-success: #17c653;
            --dashboard-danger: #f1416c;
            --dashboard-neutral: #7e8299;
            --dashboard-gradient-start: #0b4ec2;
            --dashboard-gradient-end: #35a6ff;
        }

        .dashboard-hero {
            background: linear-gradient(125deg, var(--dashboard-gradient-start) 0%, var(--dashboard-primary) 45%, var(--dashboard-gradient-end) 100%);
            box-shadow: 0 20px 45px rgba(13, 110, 253, 0.25);
            overflow: hidden;
            position: relative;
        }

        .dashboard-hero::after {
            background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.28), transparent 48%);
            content: '';
            inset: 0;
            pointer-events: none;
            position: absolute;
        }

        .dashboard-hero-content {
            position: relative;
            z-index: 1;
        }

        .dashboard-chip {
            backdrop-filter: blur(2px);
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 12px;
            color: #ffffff;
            min-width: 150px;
            padding: 12px 14px;
        }

        .dashboard-stat-card {
            border: 1px solid #f0f3ff;
            box-shadow: 0 10px 24px rgba(28, 60, 109, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .dashboard-stat-card:hover {
            box-shadow: 0 16px 30px rgba(28, 60, 109, 0.14);
            transform: translateY(-2px);
        }

        .dashboard-icon-badge {
            align-items: center;
            border-radius: 12px;
            display: inline-flex;
            height: 44px;
            justify-content: center;
            width: 44px;
        }

        .dashboard-panel {
            border: 1px solid #eef1f8;
            box-shadow: 0 12px 26px rgba(28, 60, 109, 0.08);
        }

        .dashboard-kpi-title {
            color: var(--dashboard-neutral);
            font-size: 0.95rem;
            font-weight: 600;
        }

        @media (max-width: 991.98px) {
            .dashboard-chip {
                min-width: auto;
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $tenantName = $tenant?->name ?? __('Global view');
        $monthlyDeltaLabel = ($monthlyDelta > 0 ? '+' : '').number_format($monthlyDelta);
        $monthlyDeltaTone = $monthlyDelta >= 0 ? 'badge-light-success' : 'badge-light-danger';
    @endphp

    <div class="card dashboard-hero border-0 mb-8">
        <div class="card-body p-8 p-lg-10 dashboard-hero-content">
            <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-6">
                <div>
                    <span class="badge badge-light-primary fw-bold mb-4">{{ __('Statistics') }}</span>
                    <h2 class="text-white fw-bolder mb-2">{{ __('User analytics summary') }}</h2>
                    <p class="text-white opacity-75 fw-semibold mb-0">
                        {{ __('Updated in real time from the active company context.') }}
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-3">
                    <div class="dashboard-chip">
                        <div class="text-white opacity-75 fs-8 text-uppercase fw-bold mb-1">{{ __('Current context') }}</div>
                        <div class="text-white fw-bold fs-5">{{ $tenantName }}</div>
                    </div>
                    <div class="dashboard-chip">
                        <div class="text-white opacity-75 fs-8 text-uppercase fw-bold mb-1">{{ __('Activity rate') }}</div>
                        <div class="text-white fw-bold fs-2">{{ number_format($activityRate, 1) }}%</div>
                    </div>
                    <div class="dashboard-chip">
                        <div class="text-white opacity-75 fs-8 text-uppercase fw-bold mb-1">{{ __('Verification rate') }}</div>
                        <div class="text-white fw-bold fs-2">{{ number_format($verificationRate, 1) }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-6 g-xl-8 mb-8">
        <div class="col-sm-6 col-xl-3">
            <div class="card dashboard-stat-card h-100">
                <div class="card-body p-6">
                    <div class="d-flex align-items-center justify-content-between mb-5">
                        <span class="dashboard-icon-badge bg-light-primary text-primary">
                            <i class="bi bi-people-fill fs-3"></i>
                        </span>
                        <span class="badge badge-light-primary fw-semibold">{{ __('Users') }}</span>
                    </div>
                    <div class="fs-2hx fw-bolder text-gray-900 mb-1">{{ number_format($totalUsers) }}</div>
                    <div class="dashboard-kpi-title">{{ __('Total users') }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card dashboard-stat-card h-100">
                <div class="card-body p-6">
                    <div class="d-flex align-items-center justify-content-between mb-5">
                        <span class="dashboard-icon-badge bg-light-success text-success">
                            <i class="bi bi-check-circle-fill fs-3"></i>
                        </span>
                        <span class="badge badge-light-success fw-semibold">{{ __('Active') }}</span>
                    </div>
                    <div class="fs-2hx fw-bolder text-gray-900 mb-1">{{ number_format($activeUsers) }}</div>
                    <div class="dashboard-kpi-title">{{ __('Active users') }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card dashboard-stat-card h-100">
                <div class="card-body p-6">
                    <div class="d-flex align-items-center justify-content-between mb-5">
                        <span class="dashboard-icon-badge bg-light-danger text-danger">
                            <i class="bi bi-pause-circle-fill fs-3"></i>
                        </span>
                        <span class="badge badge-light-danger fw-semibold">{{ __('Inactive') }}</span>
                    </div>
                    <div class="fs-2hx fw-bolder text-gray-900 mb-1">{{ number_format($inactiveUsers) }}</div>
                    <div class="dashboard-kpi-title">{{ __('Inactive users') }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card dashboard-stat-card h-100">
                <div class="card-body p-6">
                    <div class="d-flex align-items-center justify-content-between mb-5">
                        <span class="dashboard-icon-badge bg-light-info text-info">
                            <i class="bi bi-activity fs-3"></i>
                        </span>
                        <span class="badge badge-light-info fw-semibold">{{ __('Active') }}</span>
                    </div>
                    <div class="fs-2hx fw-bolder text-gray-900 mb-1">{{ number_format($onlineLast24h) }}</div>
                    <div class="dashboard-kpi-title">{{ __('Connected (24h)') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-6 g-xl-8">
        <div class="col-12 col-xxl-8">
            <div class="card dashboard-panel h-100">
                <div class="card-header border-0 pt-6">
                    <div class="card-title d-flex flex-column">
                        <h3 class="card-label fw-bold text-gray-900">{{ __('New users trend (last 6 months)') }}</h3>
                        <span class="text-muted mt-1 fw-semibold fs-7">{{ __('Monthly registrations compared to previous month.') }}</span>
                    </div>
                    <div class="card-toolbar">
                        <span class="badge {{ $monthlyDeltaTone }} fw-semibold">{{ __('New this month vs previous') }}: {{ $monthlyDeltaLabel }}</span>
                    </div>
                </div>
                <div class="card-body pt-2 pb-5">
                    <div id="users_growth_chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xxl-4">
            <div class="card dashboard-panel h-100">
                <div class="card-header border-0 pt-6">
                    <div class="card-title d-flex flex-column">
                        <h3 class="card-label fw-bold text-gray-900">{{ __('User status distribution') }}</h3>
                        <span class="text-muted mt-1 fw-semibold fs-7">{{ __('Active vs inactive members in your current context.') }}</span>
                    </div>
                </div>
                <div class="card-body pt-2 pb-6">
                    <div id="users_status_chart" style="height: 260px;"></div>

                    <div class="d-flex flex-column gap-3 mt-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="bullet bullet-dot bg-success"></span>
                                <span class="text-gray-700 fw-semibold">{{ __('Active users') }}</span>
                            </div>
                            <span class="fw-bold text-gray-900">{{ number_format($activeUsers) }}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="bullet bullet-dot bg-danger"></span>
                                <span class="text-gray-700 fw-semibold">{{ __('Inactive users') }}</span>
                            </div>
                            <span class="fw-bold text-gray-900">{{ number_format($inactiveUsers) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card dashboard-panel mt-8">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="fw-bold text-gray-900 mb-0">{{ __('Insights') }}</h3>
            </div>
        </div>
        <div class="card-body pt-0 pb-8">
            <div class="row g-6">
                <div class="col-md-4">
                    <div class="text-gray-700 fw-semibold mb-2">{{ __('Activity rate') }}</div>
                    <div class="progress h-8px bg-light-primary mb-2">
                        <div
                            class="progress-bar bg-primary"
                            role="progressbar"
                            style="width: {{ min(100, max(0, $activityRate)) }}%;"
                        ></div>
                    </div>
                    <span class="text-gray-900 fw-bold">{{ number_format($activityRate, 1) }}%</span>
                </div>

                <div class="col-md-4">
                    <div class="text-gray-700 fw-semibold mb-2">{{ __('Verification rate') }}</div>
                    <div class="progress h-8px bg-light-success mb-2">
                        <div
                            class="progress-bar bg-success"
                            role="progressbar"
                            style="width: {{ min(100, max(0, $verificationRate)) }}%;"
                        ></div>
                    </div>
                    <span class="text-gray-900 fw-bold">{{ number_format($verificationRate, 1) }}%</span>
                </div>

                <div class="col-md-4">
                    <div class="text-gray-700 fw-semibold mb-2">{{ __('Current month new users') }}</div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="fs-2 fw-bolder text-gray-900">{{ number_format(last($monthlySeries) ?: 0) }}</span>
                        <span class="badge {{ $monthlyDeltaTone }}">{{ __('Monthly growth') }} {{ $monthlyDeltaLabel }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('vendor_scripts')
    <script src="{{ asset('assets/vendors/apexcharts/apexcharts.min.js') }}"></script>
@endpush

@push('scripts')
    <script>
        (() => {
            if (!window.ApexCharts) {
                return;
            }

            const growthElement = document.getElementById('users_growth_chart');
            const statusElement = document.getElementById('users_status_chart');

            const growthLabels = @json($monthlyLabels);
            const growthSeries = @json($monthlySeries);
            const statusSeries = @json([$activeUsers, $inactiveUsers]);
            const hasStatusData = statusSeries.some(value => Number(value) > 0);

            if (growthElement) {
                const growthChart = new ApexCharts(growthElement, {
                    chart: {
                        type: 'area',
                        height: 300,
                        toolbar: {show: false},
                        fontFamily: 'Inter, sans-serif'
                    },
                    series: [{
                        name: @json(__('Users')),
                        data: growthSeries
                    }],
                    xaxis: {
                        categories: growthLabels,
                        labels: {
                            style: {
                                colors: '#7E8299',
                                fontSize: '12px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: '#7E8299',
                                fontSize: '12px'
                            }
                        },
                        forceNiceScale: true,
                        min: 0
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3,
                        colors: ['#0D6EFD']
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.35,
                            opacityTo: 0.02,
                            stops: [0, 100]
                        }
                    },
                    grid: {
                        borderColor: '#E4E6EF',
                        strokeDashArray: 4
                    },
                    dataLabels: {enabled: false},
                    tooltip: {
                        y: {
                            formatter: function (value) {
                                return value + ' ' + @json(__('Users'));
                            }
                        }
                    },
                    markers: {
                        size: 4,
                        strokeWidth: 0,
                        colors: ['#0D6EFD'],
                        hover: {
                            size: 6
                        }
                    }
                });

                growthChart.render();
            }

            if (statusElement) {
                const statusChart = new ApexCharts(statusElement, {
                    chart: {
                        type: 'donut',
                        height: 260,
                        fontFamily: 'Inter, sans-serif'
                    },
                    series: hasStatusData ? statusSeries : [1],
                    labels: hasStatusData
                        ? [@json(__('Active users')), @json(__('Inactive users'))]
                        : [@json(__('No users available'))],
                    colors: hasStatusData ? ['#17C653', '#F1416C'] : ['#D8E1F3'],
                    legend: {
                        show: false
                    },
                    stroke: {
                        width: 0
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '68%',
                                labels: {
                                    show: true,
                                    name: {
                                        show: true,
                                        fontSize: '13px',
                                        color: '#7E8299'
                                    },
                                    value: {
                                        show: true,
                                        fontSize: '28px',
                                        fontWeight: 700,
                                        color: '#181C32'
                                    },
                                    total: {
                                        show: true,
                                        label: @json(__('User statuses')),
                                        fontSize: '12px',
                                        color: '#7E8299',
                                        formatter: function () {
                                            if (!hasStatusData) {
                                                return '0';
                                            }

                                            return String(statusSeries.reduce((total, value) => total + Number(value), 0));
                                        }
                                    }
                                }
                            }
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    tooltip: {
                        y: {
                            formatter: function (value) {
                                return value + ' ' + @json(__('Users'));
                            }
                        }
                    }
                });

                statusChart.render();
            }
        })();
    </script>
@endpush
