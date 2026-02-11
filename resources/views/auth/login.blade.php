@extends('layouts.metronic.auth')

@section('title', __('Sign in'))

@section('content')
<div class="d-flex flex-column flex-lg-row flex-column-fluid">
    <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10 order-2 order-lg-1">
        <div class="d-flex flex-center flex-column flex-lg-row-fluid">
            <div class="w-lg-500px p-10 card shadow-sm border-0">
                <form method="POST" action="{{ route('login') }}" class="form w-100">
                    @csrf

                    <div class="text-center mb-11">
                        <h1 class="text-dark fw-bolder mb-3">{{ __('Sign In') }}</h1>

                        @if (Route::has('register'))
                            <div class="text-gray-500 fw-semibold fs-6">
                                {{ __('Need an account?') }}
                                <a class="pm-auth-link fw-bold" href="{{ route('register') }}">{{ __('Sign up') }}</a>
                            </div>
                        @endif
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger mb-8">{{ $errors->first() }}</div>
                    @endif

                    <div class="fv-row mb-8">
                        <input
                            class="form-control bg-transparent"
                            name="email"
                            placeholder="{{ __('Email') }}"
                            type="email"
                            value="{{ old('email') }}"
                            autocomplete="username"
                            required
                            autofocus
                        />
                    </div>

                    <div class="fv-row mb-3">
                        <input
                            class="form-control bg-transparent"
                            name="password"
                            placeholder="{{ __('Password') }}"
                            type="password"
                            autocomplete="current-password"
                            required
                        />
                    </div>

                    <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
                        <label class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" name="remember" type="checkbox" value="1">
                            <span class="form-check-label text-gray-700">{{ __('Remember me') }}</span>
                        </label>

                        <a href="{{ route('password.request') }}" class="pm-auth-link">{{ __('Forgot password?') }}</a>
                    </div>

                    <div class="d-grid mb-10">
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">{{ __('Sign In') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div
        class="d-flex flex-lg-row-fluid w-lg-50 bgi-size-cover bgi-position-center order-1 order-lg-2 pm-auth-hero"
        style="background-image: url('{{ asset('assets/media/images/2600x1600/fondo_playmatic.png') }}');"
    >
        <div class="w-100 h-100 pm-auth-overlay"></div>
    </div>
</div>
@endsection
