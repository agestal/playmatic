@extends('layouts.metronic.app')

@section('title', 'Edit User')

@section('content')
<div class="kt-card">
    <div class="kt-card-header">
        <h3 class="kt-card-title">Edit User</h3>
    </div>
    <div class="kt-card-content">
        <p class="text-sm text-secondary-foreground">
            User ID: {{ request()->route('user') }}
        </p>
    </div>
</div>
@endsection
