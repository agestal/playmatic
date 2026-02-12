@php
    $canAccessUsers = auth()->user()?->can('tenant.users.manage');
    $canAccessRoles = auth()->user()?->can('tenant.roles.manage');
    $canAccessPermissions = $canAccessRoles;
    $canAccessTenants = (bool) auth()->user()?->is_superadmin;
    $canAccessGamesCatalog = auth()->user()?->can('games.view.entity') || auth()->user()?->can('games.edit.entity');
    $canAccessAttendanceRounds = auth()->user()?->can('games.view.entity') || auth()->user()?->can('games.edit.content');
    $canAccessGameEntries = auth()->user()?->can('participants.view.entity') || auth()->user()?->can('games.edit.content');
    $canAccessGameWinners = auth()->user()?->can('winners.view.entity') || auth()->user()?->can('games.edit.content');

    $accessRoutesActive = request()->routeIs('users.*')
        || request()->routeIs('access.roles.*')
        || request()->routeIs('access.permissions.*')
        || request()->routeIs('platform.tenants.*');
    $gamesRoutesActive = request()->routeIs('games.index', 'games.create', 'games.edit')
        || request()->routeIs('games.entries.*')
        || request()->routeIs('games.winners.*');
@endphp

<div
    class="app-sidebar flex-column"
    data-kt-drawer="true"
    data-kt-drawer-activate="{default: true, lg: false}"
    data-kt-drawer-direction="start"
    data-kt-drawer-name="app-sidebar"
    data-kt-drawer-overlay="true"
    data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle"
    id="kt_app_sidebar"
>
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <a href="{{ route('dashboard') }}">
            <img alt="{{ config('app.name') }}" class="h-25px app-sidebar-logo-default" src="{{ asset('assets/media/app/default-logo-dark.svg') }}"/>
            <img alt="{{ config('app.name') }}" class="h-20px app-sidebar-logo-minimize" src="{{ asset('assets/media/app/mini-logo.svg') }}"/>
        </a>

        <div
            class="btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary body-bg h-30px w-30px position-absolute translate-middle start-100 top-50"
            data-kt-toggle="true"
            data-kt-toggle-name="app-sidebar-minimize"
            data-kt-toggle-state="active"
            data-kt-toggle-target="body"
            id="kt_app_sidebar_toggle"
        >
            <i class="ki-duotone ki-black-left-line fs-3 rotate-180">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </div>
    </div>

    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <div
            class="app-sidebar-wrapper hover-scroll-overlay-y my-5"
            data-kt-scroll="true"
            data-kt-scroll-activate="true"
            data-kt-scroll-dependencies="#kt_app_sidebar_logo"
            data-kt-scroll-height="auto"
            data-kt-scroll-offset="5px"
            id="kt_app_sidebar_menu_wrapper"
        >
            <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" data-kt-menu="true" id="kt_app_sidebar_menu">
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <span class="menu-icon">
                            <i class="ki-duotone ki-element-11 fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                                <span class="path4"></span>
                            </i>
                        </span>
                        <span class="menu-title">{{ __('Dashboard') }}</span>
                    </a>
                </div>

                @if ($canAccessUsers || $canAccessRoles || $canAccessPermissions || $canAccessTenants)
                    <div class="menu-item menu-accordion {{ $accessRoutesActive ? 'show' : '' }}" data-kt-menu-trigger="click">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-shield-tick fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">{{ __('Access control') }}</span>
                            <span class="menu-arrow"></span>
                        </span>

                        <div class="menu-sub menu-sub-accordion">
                            @if ($canAccessUsers)
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">{{ __('Users') }}</span>
                                    </a>
                                </div>
                            @endif

                            @if ($canAccessRoles)
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->routeIs('access.roles.*') ? 'active' : '' }}" href="{{ route('access.roles.index') }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">{{ __('Roles') }}</span>
                                    </a>
                                </div>
                            @endif

                            @if ($canAccessPermissions)
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->routeIs('access.permissions.*') ? 'active' : '' }}" href="{{ route('access.permissions.index') }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">{{ __('Permissions') }}</span>
                                    </a>
                                </div>
                            @endif

                            @if ($canAccessTenants)
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->routeIs('platform.tenants.*') ? 'active' : '' }}" href="{{ route('platform.tenants.index') }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">{{ __('Tenants') }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($canAccessAttendanceRounds)
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('games.attendance-rounds.*') ? 'active' : '' }}" href="{{ route('games.attendance-rounds.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-calendar fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">{{ __('Adivina el aforo') }}</span>
                        </a>
                    </div>
                @endif

                @if ($canAccessGamesCatalog || $canAccessGameEntries || $canAccessGameWinners)
                    <div class="menu-item menu-accordion {{ $gamesRoutesActive ? 'show' : '' }}" data-kt-menu-trigger="click">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-crown fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">{{ __('Games') }}</span>
                            <span class="menu-arrow"></span>
                        </span>

                        <div class="menu-sub menu-sub-accordion">
                            @if ($canAccessGamesCatalog)
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->routeIs('games.index', 'games.create', 'games.edit') ? 'active' : '' }}" href="{{ route('games.index') }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">{{ __('Games') }}</span>
                                    </a>
                                </div>
                            @endif

                            @if ($canAccessGameEntries)
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->routeIs('games.entries.*') ? 'active' : '' }}" href="{{ route('games.entries.index') }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">{{ __('Entries') }}</span>
                                    </a>
                                </div>
                            @endif

                            @if ($canAccessGameWinners)
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->routeIs('games.winners.*') ? 'active' : '' }}" href="{{ route('games.winners.index') }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">{{ __('Winners') }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
