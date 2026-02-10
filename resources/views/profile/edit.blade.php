@extends('layouts.metronic.app')

@section('title', __('Profile'))

@section('content')
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">{{ __('Profile') }}</h3>
        </div>
        <div class="kt-card-content p-5">
            <p class="text-sm text-secondary-foreground">{{ __('Manage your account settings.') }}</p>
        </div>
    </div>
@endsection
