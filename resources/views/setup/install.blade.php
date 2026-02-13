@extends('layouts.metronic.auth')

@section('title', __('Initial setup'))

@section('content')
<div class="d-flex flex-column flex-lg-row flex-column-fluid">
    <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10 order-2 order-lg-1">
        <div class="d-flex flex-center flex-column flex-lg-row-fluid">
            <div class="w-lg-700px p-10 card shadow-sm border-0">
                <form method="POST" action="{{ route('install.store') }}" class="form w-100">
                    @csrf

                    <div class="text-center mb-10">
                        <h1 class="text-dark fw-bolder mb-3">{{ __('Initial setup') }}</h1>
                        <div class="text-gray-500 fw-semibold fs-6">
                            {{ __('Configure the first superadmin and primary company to start using Playmatic.') }}
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger mb-8">{{ $errors->first() }}</div>
                    @endif

                    <div class="separator separator-dashed my-8"></div>
                    <h4 class="fw-bold mb-6">{{ __('Superadmin') }}</h4>

                    <div class="row g-6 mb-10">
                        <div class="col-12 col-md-6">
                            <label class="required form-label fw-semibold">{{ __('Name') }}</label>
                            <input
                                class="form-control bg-transparent"
                                name="admin_name"
                                type="text"
                                value="{{ old('admin_name') }}"
                                required
                            />
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="required form-label fw-semibold">{{ __('Email') }}</label>
                            <input
                                class="form-control bg-transparent"
                                name="admin_email"
                                type="email"
                                value="{{ old('admin_email') }}"
                                autocomplete="username"
                                required
                            />
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="required form-label fw-semibold">{{ __('Password') }}</label>
                            <input
                                class="form-control bg-transparent"
                                name="admin_password"
                                type="password"
                                autocomplete="new-password"
                                required
                            />
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="required form-label fw-semibold">{{ __('Confirm Password') }}</label>
                            <input
                                class="form-control bg-transparent"
                                name="admin_password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                required
                            />
                        </div>
                    </div>

                    <div class="separator separator-dashed my-8"></div>
                    <h4 class="fw-bold mb-6">{{ __('Primary company') }}</h4>

                    <div class="row g-6">
                        <div class="col-12 col-md-6">
                            <label class="required form-label fw-semibold">{{ __('Name') }}</label>
                            <input
                                class="form-control bg-transparent"
                                name="tenant_name"
                                type="text"
                                value="{{ old('tenant_name') }}"
                                required
                            />
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="required form-label fw-semibold">{{ __('Primary domain') }}</label>
                            <input
                                class="form-control bg-transparent"
                                name="primary_domain"
                                type="text"
                                value="{{ old('primary_domain', $defaultDomain) }}"
                                placeholder="app.example.com"
                                required
                            />
                            <div class="form-text">{{ __('Use the main domain where users will sign in, for example :example.', ['example' => 'app.example.com']) }}</div>
                        </div>
                    </div>

                    <div class="d-grid mt-10">
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">{{ __('Install') }}</span>
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
