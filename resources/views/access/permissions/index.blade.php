@extends('layouts.metronic.app')

@section('title', __('Permissions'))

@section('content')
    <x-tables.panel
        :title="__('Permissions')"
        :description="__('Permission catalog available with collapsible filters and standardized actions.')"
    >
        <livewire:permission-table />
    </x-tables.panel>
@endsection
