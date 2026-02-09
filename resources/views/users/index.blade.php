@extends('layouts.metronic.app')

@section('title', 'Users')

@section('content')
<div class="kt-card">
    <div class="kt-card-header border-b border-gray-200">
        <h3 class="kt-card-title">Users Management</h3>
        <div class="kt-card-toolbar">
            <a href="#" class="kt-btn kt-btn-primary kt-btn-sm">
                <i class="ki-filled ki-plus"></i>
                Add User
            </a>
        </div>
    </div>
    <div class="kt-card-body">
        <livewire:users-table />
    </div>
</div>
@endsection
