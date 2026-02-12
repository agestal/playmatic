<meta charset="utf-8"/>
<title>@yield('title', config('app.name'))</title>
<meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport"/>
<meta content="{{ csrf_token() }}" name="csrf-token"/>

<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/media/app/apple-touch-icon.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/media/app/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/media/app/favicon-16x16.png') }}">
<link rel="shortcut icon" href="{{ asset('assets/media/app/favicon.ico') }}">

<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700"/>
<link rel="stylesheet" href="{{ asset('assets/metronic8/plugins/global/plugins.bundle.css') }}" type="text/css"/>
<link rel="stylesheet" href="{{ asset('assets/metronic8/css/style.bundle.css') }}" type="text/css"/>
<link rel="stylesheet" href="{{ asset('assets/css/metronic8-overrides.css') }}" type="text/css"/>

@php
    $tenantTheme = \App\Support\Theme\TenantTheme::fromTenant($currentTenant ?? null);
@endphp
<style id="pm-tenant-theme">
    :root {
        --pm-primary: {{ $tenantTheme['primary'] }};
        --pm-primary-rgb: {{ $tenantTheme['primary_rgb'] }};
        --pm-primary-soft: {{ $tenantTheme['primary_light'] }};
        --pm-secondary: {{ $tenantTheme['secondary'] }};
        --pm-secondary-rgb: {{ $tenantTheme['secondary_rgb'] }};
        --pm-secondary-soft: {{ $tenantTheme['secondary_light'] }};
        --pm-neutral: {{ $tenantTheme['neutral'] }};
        --pm-border-soft: {{ $tenantTheme['border_soft'] }};
        --pm-border-strong: {{ $tenantTheme['border_strong'] }};
        --pm-surface-start: {{ $tenantTheme['surface_start'] }};
        --pm-surface-end: {{ $tenantTheme['surface_end'] }};
        --pm-gradient-end: {{ $tenantTheme['gradient_end'] }};

        --bs-primary: {{ $tenantTheme['primary'] }};
        --bs-primary-rgb: {{ $tenantTheme['primary_rgb'] }};
        --bs-primary-active: {{ $tenantTheme['primary_active'] }};
        --bs-primary-active-rgb: {{ $tenantTheme['primary_active_rgb'] }};
        --bs-primary-light: {{ $tenantTheme['primary_light'] }};
        --bs-primary-inverse: {{ $tenantTheme['primary_inverse'] }};
        --bs-primary-clarity: {{ $tenantTheme['primary_clarity'] }};
        --bs-primary-text-emphasis: {{ $tenantTheme['primary_text_emphasis'] }};
        --bs-primary-bg-subtle: {{ $tenantTheme['primary_bg_subtle'] }};
        --bs-primary-border-subtle: {{ $tenantTheme['primary_border_subtle'] }};

        --bs-secondary: {{ $tenantTheme['secondary'] }};
        --bs-secondary-rgb: {{ $tenantTheme['secondary_rgb'] }};
        --bs-secondary-active: {{ $tenantTheme['secondary_active'] }};
        --bs-secondary-active-rgb: {{ $tenantTheme['secondary_active_rgb'] }};
        --bs-secondary-light: {{ $tenantTheme['secondary_light'] }};
        --bs-secondary-inverse: {{ $tenantTheme['secondary_inverse'] }};
        --bs-secondary-clarity: {{ $tenantTheme['secondary_clarity'] }};
        --bs-secondary-text-emphasis: {{ $tenantTheme['secondary_text_emphasis'] }};
        --bs-secondary-bg-subtle: {{ $tenantTheme['secondary_bg_subtle'] }};
        --bs-secondary-border-subtle: {{ $tenantTheme['secondary_border_subtle'] }};

        --bs-link-color: {{ $tenantTheme['primary'] }};
        --bs-link-color-rgb: {{ $tenantTheme['primary_rgb'] }};
        --bs-link-hover-color: {{ $tenantTheme['link_hover'] }};
        --bs-focus-ring-color: {{ $tenantTheme['focus_ring'] }};
    }
</style>

@stack('styles')
@stack('m8_styles')

@livewireStyles
