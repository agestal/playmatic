@php
    $user = auth()->user();

    $currentRoute = request()->route();
    $routeName = $currentRoute?->getName();
    $routeParameters = $currentRoute?->parameters() ?? [];
    $localePattern = data_get($currentRoute?->wheres, 'locale', '');
    $supportedLocales = collect(explode('|', (string) $localePattern))
        ->map(fn (string $locale): string => trim($locale))
        ->filter()
        ->values();

    if ($supportedLocales->isEmpty()) {
        $supportedLocales = collect([app()->getLocale()]);
    }

    $currentLocale = app()->getLocale();
    $localeLabels = [
        'en' => __('English'),
        'es' => __('Spanish'),
        'pt' => __('Portuguese'),
    ];

    $localeFlags = [
        'en' => 'united-states',
        'es' => 'spain',
        'pt' => 'portugal',
    ];

    $userInitials = collect(preg_split('/\s+/', trim((string) $user?->name)) ?: [])
        ->filter()
        ->map(static fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
        ->take(2)
        ->implode('');

    if ($userInitials === '') {
        $userInitials = 'U';
    }
@endphp

<div
    class="app-header"
    id="kt_app_header"
    data-kt-sticky="true"
    data-kt-sticky-activate="{default: true, lg: true}"
    data-kt-sticky-animation="false"
    data-kt-sticky-name="app-header-minimize"
    data-kt-sticky-offset="{default: '200px', lg: '0'}"
>
    <div class="app-container container-fluid d-flex align-items-center justify-content-between" id="kt_app_header_container">
        <div class="d-flex align-items-center d-lg-none ms-n3 me-1 me-md-2" title="{{ __('Show sidebar menu') }}">
            <div class="btn btn-icon btn-active-color-primary w-35px h-35px" id="kt_app_sidebar_mobile_toggle">
                <i class="ki-duotone ki-abstract-14 fs-2 fs-md-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
            </div>
        </div>

        <div class="d-flex align-items-center d-lg-none">
            <a class="d-lg-none" href="{{ route('dashboard') }}">
                <img alt="{{ config('app.name') }}" class="h-30px" src="{{ asset('assets/media/app/logo-32.svg') }}"/>
            </a>
        </div>

        <div class="app-navbar flex-shrink-0 ms-auto">
            @isset($currentTenant)
                <div class="app-navbar-item ms-1 ms-lg-3 d-none d-sm-flex">
                    <span class="badge badge-light-primary fw-semibold">{{ $currentTenant->name }}</span>
                </div>
            @endisset

            <div class="app-navbar-item ms-1 ms-lg-3" id="kt_header_user_menu_toggle">
                <div
                    class="cursor-pointer symbol symbol-35px"
                    data-kt-menu-attach="parent"
                    data-kt-menu-placement="bottom-end"
                    data-kt-menu-trigger="click"
                >
                    <div class="symbol-label fs-6 fw-bold bg-light-primary text-primary">{{ $userInitials }}</div>
                </div>

                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-primary fw-semibold py-4 fs-6 w-275px" data-kt-menu="true">
                    <div class="menu-item px-3">
                        <div class="menu-content d-flex align-items-center px-3">
                            <div class="symbol symbol-50px me-5">
                                <div class="symbol-label fs-5 fw-bold bg-light-primary text-primary">{{ $userInitials }}</div>
                            </div>

                            <div class="d-flex flex-column">
                                <div class="fw-bold d-flex align-items-center fs-5">{{ $user?->name }}</div>
                                <span class="fw-semibold text-muted text-hover-primary fs-7">{{ $user?->email }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="separator my-2"></div>

                    @isset($currentTenant)
                        <div class="menu-item px-5">
                            <span class="menu-link px-5 text-muted">{{ __('Active company: :name', ['name' => $currentTenant->name]) }}</span>
                        </div>
                    @endisset

                    <div class="menu-item px-5">
                        <a class="menu-link px-5" href="{{ route('profile.edit') }}">{{ __('Profile') }}</a>
                    </div>

                    <div class="menu-item px-5">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="menu-link px-5 border-0 bg-transparent w-100 text-start" type="submit">{{ __('Log out') }}</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="app-navbar-item ms-1 ms-lg-3">
                <div
                    class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-35px h-35px"
                    data-kt-menu-attach="parent"
                    data-kt-menu-placement="bottom-end"
                    data-kt-menu-trigger="click"
                >
                    @if (isset($localeFlags[$currentLocale]))
                        <img alt="{{ $localeLabels[$currentLocale] ?? strtoupper($currentLocale) }}" class="rounded-1" src="{{ asset('assets/media/flags/'.$localeFlags[$currentLocale].'.svg') }}" style="width:18px;height:18px;"/>
                    @else
                        <span class="fs-8 fw-bold">{{ strtoupper($currentLocale) }}</span>
                    @endif
                </div>

                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-primary fw-semibold py-4 fs-7 w-175px" data-kt-menu="true">
                    @foreach ($supportedLocales as $locale)
                        @php
                            $isActiveLocale = $locale === $currentLocale;
                            $localeLabel = $localeLabels[$locale] ?? strtoupper($locale);
                            $localeFlag = $localeFlags[$locale] ?? null;
                            $localeUrl = url('/'.$locale);

                            if ($routeName) {
                                try {
                                    $localeUrl = route($routeName, array_merge($routeParameters, ['locale' => $locale], request()->query()), false);
                                } catch (\Throwable) {
                                    $localeUrl = url('/'.$locale);
                                }
                            }
                        @endphp

                        <div class="menu-item px-3">
                            <a class="menu-link d-flex px-5 {{ $isActiveLocale ? 'active' : '' }}" href="{{ $localeUrl }}">
                                <span class="symbol symbol-20px me-3">
                                    @if ($localeFlag)
                                        <img alt="{{ $localeLabel }}" class="rounded-1" src="{{ asset('assets/media/flags/'.$localeFlag.'.svg') }}"/>
                                    @endif
                                </span>
                                {{ $localeLabel }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
