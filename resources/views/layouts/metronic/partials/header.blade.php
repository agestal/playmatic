@php
    $user = auth()->user();
    $pageTitle = trim($__env->yieldContent('page_title')) ?: trim($__env->yieldContent('title')) ?: config('app.name');

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
    ];
    $localeFlags = [
        'en' => 'united-states',
        'es' => 'spain',
    ];

    $userInitials = collect(preg_split('/\s+/', trim((string) $user?->name)) ?: [])
        ->filter()
        ->map(static fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
        ->take(2)
        ->implode('');

    if ($userInitials === '') {
        $userInitials = 'U';
    }

    $quickLinks = [
        [
            'title' => __('Dashboard'),
            'icon' => 'ki-element-11',
            'url' => route('dashboard'),
            'active' => request()->routeIs('dashboard'),
            'show' => true,
        ],
        [
            'title' => __('Users'),
            'icon' => 'ki-profile-user',
            'url' => route('users.index'),
            'active' => request()->routeIs('users.*'),
            'show' => (bool) $user?->can('tenant.users.manage'),
        ],
        [
            'title' => __('Roles'),
            'icon' => 'ki-setting-4',
            'url' => route('access.roles.index'),
            'active' => request()->routeIs('access.roles.*'),
            'show' => (bool) $user?->can('tenant.roles.manage'),
        ],
        [
            'title' => __('Tenants'),
            'icon' => 'ki-setting',
            'url' => route('platform.tenants.index'),
            'active' => request()->routeIs('platform.tenants.*'),
            'show' => (bool) $user?->is_superadmin,
        ],
    ];
@endphp

<header
    class="kt-header fixed top-0 z-10 start-0 end-0 flex items-stretch shrink-0 bg-background"
    data-kt-sticky="true"
    data-kt-sticky-class="border-b border-border"
    data-kt-sticky-name="header"
    id="header"
>
    <div class="kt-container-fluid flex items-center justify-between gap-4" id="headerContainer">
        <div class="flex items-center gap-2.5 lg:gap-4 min-w-0">
            <div class="flex lg:hidden items-center gap-2">
                <a class="shrink-0" href="{{ route('dashboard') }}">
                    <img class="max-h-[24px] w-full" src="{{ asset('assets/media/app/mini-logo.svg') }}" alt="{{ config('app.name') }}">
                </a>

                <button class="kt-btn kt-btn-icon kt-btn-ghost" data-kt-drawer-toggle="#sidebar" type="button" aria-label="{{ __('Open menu') }}">
                    <i class="ki-filled ki-menu"></i>
                </button>
            </div>

            <div class="hidden lg:flex items-center gap-3 min-w-0">
                <h1 class="truncate text-sm font-semibold text-mono">{{ $pageTitle }}</h1>

                @isset($currentTenant)
                    <span class="kt-badge kt-badge-sm kt-badge-light-primary">{{ $currentTenant->name }}</span>
                @endisset
            </div>
        </div>

        <div class="hidden xl:flex items-center min-w-0">
            <div class="kt-menu flex-row gap-1" data-kt-menu="true" id="header_quick_menu">
                @foreach ($quickLinks as $link)
                    @continue(! $link['show'])

                    <div class="kt-menu-item">
                        <a
                            class="kt-menu-link gap-2 rounded-md px-3 py-2 text-2sm font-medium {{ $link['active'] ? 'bg-primary/10 text-primary' : 'text-secondary-foreground hover:text-primary hover:bg-accent' }}"
                            href="{{ $link['url'] }}"
                        >
                            <i class="ki-filled {{ $link['icon'] }} text-base"></i>
                            <span class="kt-menu-title">{{ $link['title'] }}</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <button
                class="kt-btn kt-btn-icon kt-btn-ghost"
                data-kt-theme-switch-toggle="true"
                data-kt-theme-switch-state="dark"
                type="button"
                aria-label="{{ __('Toggle dark mode') }}"
            >
                <i class="ki-filled ki-moon text-lg"></i>
            </button>

            <div
                class="shrink-0"
                data-kt-dropdown="true"
                data-kt-dropdown-offset="0, 10px"
                data-kt-dropdown-placement="bottom-end"
                data-kt-dropdown-placement-rtl="bottom-start"
                data-kt-dropdown-trigger="click"
            >
                <button class="kt-btn kt-btn-sm kt-btn-light gap-2" data-kt-dropdown-toggle="true" type="button">
                    @if (isset($localeFlags[$currentLocale]))
                        <img class="size-4 rounded-full" src="{{ asset('assets/media/flags/'.$localeFlags[$currentLocale].'.svg') }}" alt="{{ $localeLabels[$currentLocale] ?? strtoupper($currentLocale) }}">
                    @endif
                    <span class="hidden sm:inline">{{ $localeLabels[$currentLocale] ?? strtoupper($currentLocale) }}</span>
                    <i class="ki-filled ki-down text-2xs"></i>
                </button>

                <div class="kt-dropdown-menu w-[190px]" data-kt-dropdown-menu="true">
                    <ul class="kt-dropdown-menu-sub">
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

                            <li class="{{ $isActiveLocale ? 'active' : '' }}">
                                <a class="kt-dropdown-menu-link" href="{{ $localeUrl }}">
                                    <span class="flex items-center gap-2">
                                        @if ($localeFlag)
                                            <img class="size-4 rounded-full" src="{{ asset('assets/media/flags/'.$localeFlag.'.svg') }}" alt="{{ $localeLabel }}">
                                        @endif
                                        <span class="kt-menu-title">{{ $localeLabel }}</span>
                                    </span>

                                    @if ($isActiveLocale)
                                        <i class="ki-solid ki-check-circle ms-auto text-green-500 text-base"></i>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div
                class="shrink-0"
                data-kt-dropdown="true"
                data-kt-dropdown-offset="0, 10px"
                data-kt-dropdown-placement="bottom-end"
                data-kt-dropdown-placement-rtl="bottom-start"
                data-kt-dropdown-trigger="click"
            >
                <button class="cursor-pointer" data-kt-dropdown-toggle="true" type="button">
                    <span class="inline-flex size-9 items-center justify-center rounded-full bg-primary/10 text-primary font-semibold text-sm border border-primary/20">
                        {{ $userInitials }}
                    </span>
                </button>

                <div class="kt-dropdown-menu w-[270px]" data-kt-dropdown-menu="true">
                    <div class="px-2.5 py-2.5">
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex size-10 items-center justify-center rounded-full bg-primary/10 text-primary font-semibold border border-primary/20">
                                {{ $userInitials }}
                            </span>
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-mono truncate">{{ $user?->name }}</div>
                                <div class="text-xs text-secondary-foreground truncate">{{ $user?->email }}</div>
                            </div>
                        </div>
                    </div>

                    <ul class="kt-dropdown-menu-sub">
                        <li>
                            <div class="kt-dropdown-menu-separator"></div>
                        </li>
                        <li>
                            <a class="kt-dropdown-menu-link" href="{{ route('dashboard') }}">
                                <i class="ki-filled ki-element-11"></i>
                                {{ __('Dashboard') }}
                            </a>
                        </li>
                        @isset($currentTenant)
                            <li>
                                <a class="kt-dropdown-menu-link" href="{{ route('profile.edit') }}">
                                    <i class="ki-filled ki-profile-circle"></i>
                                    {{ __('Profile') }}
                                </a>
                            </li>
                        @endisset
                        <li>
                            <div class="kt-dropdown-menu-separator"></div>
                        </li>
                        <li>
                            <label class="kt-dropdown-menu-link cursor-pointer">
                                <span class="flex items-center gap-2">
                                    <i class="ki-filled ki-moon"></i>
                                    {{ __('Dark mode') }}
                                </span>
                                <input
                                    class="ms-auto kt-switch"
                                    data-kt-theme-switch-state="dark"
                                    data-kt-theme-switch-toggle="true"
                                    type="checkbox"
                                    value="1"
                                >
                            </label>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="kt-dropdown-menu-link w-full text-start" type="submit">
                                    <i class="ki-filled ki-exit-right"></i>
                                    {{ __('Log out') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>
