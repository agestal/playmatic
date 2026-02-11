<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.metronic.partials.head')
</head>

<body class="antialiased flex h-full text-base text-foreground bg-background demo1 kt-sidebar-fixed kt-header-fixed pm-metronic-app">
    <div class="flex grow">
        @include('layouts.metronic.partials.sidebar')

        <div class="kt-wrapper flex flex-col grow min-w-0">
            @include('layouts.metronic.partials.header')
            @include('layouts.metronic.partials.content')
            @include('layouts.metronic.partials.footer')
        </div>
    </div>

    @include('layouts.metronic.partials.scripts')
</body>
</html>
