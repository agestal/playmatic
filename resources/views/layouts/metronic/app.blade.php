<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.metronic.partials.head')
</head>
<body
    id="kt_app_body"
    data-kt-app-header-fixed="true"
    data-kt-app-layout="dark-sidebar"
    data-kt-app-sidebar-enabled="true"
    data-kt-app-sidebar-fixed="true"
    data-kt-app-sidebar-push-header="true"
    data-kt-app-sidebar-hoverable="true"
    class="app-default"
>
<script>
    var defaultThemeMode = 'light';
    var themeMode;

    if (document.documentElement) {
        if (document.documentElement.hasAttribute('data-bs-theme-mode')) {
            themeMode = document.documentElement.getAttribute('data-bs-theme-mode');
        } else {
            if (localStorage.getItem('data-bs-theme') !== null) {
                themeMode = localStorage.getItem('data-bs-theme');
            } else {
                themeMode = defaultThemeMode;
            }
        }

        if (themeMode === 'system') {
            themeMode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }

        document.documentElement.setAttribute('data-bs-theme', themeMode);
    }
</script>

<div class="d-flex flex-column flex-root app-root" id="kt_app_root">
    <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
        @include('layouts.metronic.partials.header')

        <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
            @include('layouts.metronic.partials.sidebar')
            @include('layouts.metronic.partials.content')
        </div>
    </div>
</div>

@include('layouts.metronic.partials.scripts')
</body>
</html>
