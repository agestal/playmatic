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

@stack('styles')
@stack('m8_styles')

@livewireStyles
