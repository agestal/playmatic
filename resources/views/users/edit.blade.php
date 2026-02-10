@extends('layouts.metronic.app')

@section('title', __('Edit user'))

@section('content')
<div class="kt-card">
    <div class="kt-card-header">
        <h3 class="kt-card-title">{{ __('Edit user') }}</h3>
    </div>
    <div class="kt-card-content">
        <p class="text-sm text-secondary-foreground">
            {{ __('User ID: :id', ['id' => request()->route('user')]) }}
        </p>
    </div>
</div>
@endsection
