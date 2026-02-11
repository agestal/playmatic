@extends('layouts.metronic.auth')

@section('content')
    <div class="d-flex flex-column flex-lg-row flex-column-fluid">
        <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10 order-2 order-lg-1">
            <div class="d-flex flex-center flex-column flex-lg-row-fluid">
                <div class="w-lg-500px p-10 card shadow-sm border-0">
                    {{ $slot }}
                </div>
            </div>
        </div>

        <div
            class="d-flex flex-lg-row-fluid w-lg-50 bgi-size-cover bgi-position-center order-1 order-lg-2 pm-auth-hero"
            style="background-image: url('{{ asset('assets/media/images/2600x1600/fondo_playmatic.png') }}');"
        >
            <div class="w-100 h-100 pm-auth-overlay d-flex align-items-end p-10">
                <div class="text-white fw-semibold fs-4">{{ config('app.name') }}</div>
            </div>
        </div>
    </div>
@endsection
