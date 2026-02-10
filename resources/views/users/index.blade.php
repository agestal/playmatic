@extends('layouts.metronic.app')

@section('title', __('Users'))

@section('content')
    <x-tables.panel
        :title="__('Users')"
        :description="__('Manage users in the active company with standardized filters and quick actions.')"
    >
        <livewire:user-table />
    </x-tables.panel>
@endsection
