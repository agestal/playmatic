<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.metronic.partials.head')
</head>

<body class="antialiased flex h-full grow text-base text-foreground bg-background demo1 app-blank">
    @yield('content')

    @include('layouts.metronic.partials.scripts')
</body>
</html>
