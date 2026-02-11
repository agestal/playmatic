@extends('layouts.metronic.app')

@section('title', __('Edit user'))

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ __('Edit user') }}</h3>
    </div>
    <div class="card-body">
        <p class="text-muted mb-0">{{ __('User ID: :id', ['id' => request()->route('user')]) }}</p>
    </div>
</div>
@endsection
