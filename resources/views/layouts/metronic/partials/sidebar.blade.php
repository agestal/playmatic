@php
    $canAccessUsers = auth()->user()?->can('tenant.users.manage');
    $canAccessRoles = auth()->user()?->can('tenant.roles.manage');
    $canAccessPermissions = $canAccessRoles;
    $canAccessTenants = (bool) auth()->user()?->is_superadmin;
    $showAccessControl = $canAccessUsers || $canAccessRoles || $canAccessPermissions || $canAccessTenants;

    $accessRoutesActive = request()->routeIs('users.*')
        || request()->routeIs('access.roles.*')
        || request()->routeIs('access.permissions.*')
        || request()->routeIs('platform.tenants.*');
@endphp

<div
    class="kt-sidebar bg-background border-e border-e-border fixed top-0 bottom-0 z-20 hidden lg:flex flex-col items-stretch shrink-0 [--kt-drawer-enable:true] lg:[--kt-drawer-enable:false]"
    data-kt-drawer="true"
    data-kt-drawer-class="kt-drawer kt-drawer-start top-0 bottom-0"
    id="sidebar"
>
    <div class="kt-sidebar-header hidden lg:flex items-center relative justify-between px-3 lg:px-6 shrink-0" id="sidebar_header">
        <a class="dark:hidden" href="{{ route('dashboard') }}">
            <img class="default-logo min-h-[22px] max-w-none" src="{{ asset('assets/media/app/default-logo.svg') }}" alt="{{ config('app.name') }}">
            <img class="small-logo min-h-[22px] max-w-none" src="{{ asset('assets/media/app/mini-logo.svg') }}" alt="{{ config('app.name') }}">
        </a>

        <a class="hidden dark:block" href="{{ route('dashboard') }}">
            <img class="default-logo min-h-[22px] max-w-none" src="{{ asset('assets/media/app/default-logo-dark.svg') }}" alt="{{ config('app.name') }}">
            <img class="small-logo min-h-[22px] max-w-none" src="{{ asset('assets/media/app/mini-logo.svg') }}" alt="{{ config('app.name') }}">
        </a>

        <button
            class="kt-btn kt-btn-outline kt-btn-icon size-[30px] absolute start-full top-2/4 -translate-x-2/4 -translate-y-2/4 rtl:translate-x-2/4"
            data-kt-toggle="body"
            data-kt-toggle-class="kt-sidebar-collapse"
            id="sidebar_toggle"
            type="button"
            aria-label="{{ __('Collapse sidebar') }}"
        >
            <i class="ki-filled ki-black-left-line kt-toggle-active:rotate-180 transition-all duration-300 rtl:translate rtl:rotate-180 rtl:kt-toggle-active:rotate-0"></i>
        </button>
    </div>

    <div class="kt-sidebar-content flex grow shrink-0 py-5 pe-2" id="sidebar_content">
        <div
            class="kt-scrollable-y-hover grow shrink-0 flex ps-2 lg:ps-5 pe-1 lg:pe-3"
            data-kt-scrollable="true"
            data-kt-scrollable-dependencies="#sidebar_header"
            data-kt-scrollable-height="auto"
            data-kt-scrollable-offset="0px"
            data-kt-scrollable-wrappers="#sidebar_content"
            id="sidebar_scrollable"
        >
            <div class="kt-menu flex flex-col grow gap-1" data-kt-menu="true" data-kt-menu-accordion-expand-all="false" id="sidebar_menu">
                <div class="kt-menu-item">
                    <a
                        class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px] {{ request()->routeIs('dashboard') ? 'bg-primary/10 text-primary rounded-md' : '' }}"
                        href="{{ route('dashboard') }}"
                    >
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled ki-element-11 text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium">{{ __('Dashboard') }}</span>
                    </a>
                </div>

                @if ($showAccessControl)
                    <div class="kt-menu-item {{ $accessRoutesActive ? 'show' : '' }}" id="access_control_menu_item">
                        <a
                            class="kt-menu-link kt-menu-toggle w-full text-start flex items-center grow border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px] {{ $accessRoutesActive ? 'bg-primary/10 text-primary rounded-md' : '' }}"
                            href="#"
                            role="button"
                            aria-expanded="{{ $accessRoutesActive ? 'true' : 'false' }}"
                            id="access_control_menu_toggle"
                        >
                            <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                                <i class="ki-filled ki-shield-tick text-lg"></i>
                            </span>
                            <span class="kt-menu-title text-sm font-medium">{{ __('Access control') }}</span>
                            <span class="kt-menu-arrow">
                                <i class="ki-filled ki-right text-2xs"></i>
                            </span>
                        </a>

                        <div class="kt-menu-accordion gap-1 ms-[18px] border-s border-border/60 ps-[8px] {{ $accessRoutesActive ? 'show' : '' }}" id="access_control_menu_submenu">
                            @if ($canAccessUsers)
                                <div class="kt-menu-item">
                                    <a
                                        class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[20px] pe-[10px] py-[6px] {{ request()->routeIs('users.*') ? 'bg-primary/10 text-primary rounded-md' : '' }}"
                                        href="{{ route('users.index') }}"
                                    >
                                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                                            <i class="ki-filled ki-profile-user text-lg"></i>
                                        </span>
                                        <span class="kt-menu-title text-sm font-medium">{{ __('Users') }}</span>
                                    </a>
                                </div>
                            @endif

                            @if ($canAccessRoles)
                                <div class="kt-menu-item">
                                    <a
                                        class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[20px] pe-[10px] py-[6px] {{ request()->routeIs('access.roles.*') ? 'bg-primary/10 text-primary rounded-md' : '' }}"
                                        href="{{ route('access.roles.index') }}"
                                    >
                                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                                            <i class="ki-filled ki-setting-4 text-lg"></i>
                                        </span>
                                        <span class="kt-menu-title text-sm font-medium">{{ __('Roles') }}</span>
                                    </a>
                                </div>
                            @endif

                            @if ($canAccessPermissions)
                                <div class="kt-menu-item">
                                    <a
                                        class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[20px] pe-[10px] py-[6px] {{ request()->routeIs('access.permissions.*') ? 'bg-primary/10 text-primary rounded-md' : '' }}"
                                        href="{{ route('access.permissions.index') }}"
                                    >
                                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                                            <i class="ki-filled ki-shield-tick text-lg"></i>
                                        </span>
                                        <span class="kt-menu-title text-sm font-medium">{{ __('Permissions') }}</span>
                                    </a>
                                </div>
                            @endif

                            @if ($canAccessTenants)
                                <div class="kt-menu-item">
                                    <a
                                        class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[20px] pe-[10px] py-[6px] {{ request()->routeIs('platform.tenants.*') ? 'bg-primary/10 text-primary rounded-md' : '' }}"
                                        href="{{ route('platform.tenants.index') }}"
                                    >
                                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                                            <i class="ki-filled ki-setting text-lg"></i>
                                        </span>
                                        <span class="kt-menu-title text-sm font-medium">{{ __('Tenants') }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @isset($currentTenant)
                    <div class="mt-4 px-[10px] py-2 text-xs text-secondary-foreground border-t border-border">
                        {{ __('Active company:') }}
                        <span class="font-semibold text-mono">{{ $currentTenant->name }}</span>
                    </div>
                @endisset
            </div>
        </div>
    </div>
</div>
