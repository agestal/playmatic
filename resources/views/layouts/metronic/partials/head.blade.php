<meta charset="utf-8">
<title>@yield('title', config('app.name'))</title>

<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="csrf-token" content="{{ csrf_token() }}">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">

<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/media/app/apple-touch-icon.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/media/app/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/media/app/favicon-16x16.png') }}">
<link rel="shortcut icon" href="{{ asset('assets/media/app/favicon.ico') }}">

<link rel="stylesheet" href="{{ asset('assets/vendors/apexcharts/apexcharts.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendors/keenicons/styles.bundle.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/metronic-app-overrides.css') }}">

@livewireStyles
@stack('styles')
