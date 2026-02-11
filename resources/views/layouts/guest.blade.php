@extends('layouts.metronic.auth')

@section('content')
    <div class="pm-auth-shell">
        <div class="pm-auth-left">
            <div class="pm-auth-card">
                <div class="kt-card">
                    <div class="kt-card-content p-6 lg:p-7">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>

        <div class="pm-auth-right" aria-hidden="true">
            <div class="pm-auth-right-content">
                <h2>{{ config('app.name') }}</h2>
                <p>{{ __('Manage users, roles, permissions and tenants from a single workspace.') }}</p>
            </div>
        </div>
    </div>
@endsection
