<div class="app-footer py-4 d-flex flex-column flex-md-row flex-center flex-md-stack" id="kt_app_footer">
    <div class="text-dark order-2 order-md-1">
        <span class="text-muted fw-semibold me-1">{{ date('Y') }}&copy;</span>
        <a class="text-gray-800 text-hover-primary" href="{{ route('dashboard') }}">{{ config('app.name') }}</a>
    </div>

    <ul class="menu menu-gray-600 menu-hover-primary fw-semibold order-1">
        <li class="menu-item"><a class="menu-link px-2" href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
        @isset($currentTenant)
            <li class="menu-item"><a class="menu-link px-2" href="{{ route('profile.edit') }}">{{ __('Profile') }}</a></li>
        @endisset
        <li class="menu-item"><a class="menu-link px-2" href="https://preview.keenthemes.com/metronic8/demo1" rel="noopener noreferrer" target="_blank">Metronic 8</a></li>
    </ul>
</div>
