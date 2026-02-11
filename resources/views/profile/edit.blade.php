@extends('layouts.metronic.app')

@section('title', __('Profile'))

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('Profile') }}</h3>
        </div>
        <div class="card-body">
            <p class="text-muted mb-0">{{ __('Manage your account settings.') }}</p>
        </div>
    </div>
@endsection
