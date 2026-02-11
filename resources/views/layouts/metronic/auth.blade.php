<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.metronic.partials.head')
</head>
<body id="kt_app_body" class="app-blank">
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

<div class="d-flex flex-column flex-root" id="kt_app_root">
    @yield('content')
</div>

@include('layouts.metronic.partials.scripts')
</body>
</html>
