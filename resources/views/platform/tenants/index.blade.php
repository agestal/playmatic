@extends('layouts.metronic.app')

@section('title', __('Tenants'))

@section('content')
    <x-tables.panel
        :title="__('Tenants')"
        :description="__('Centralized tenant list with collapsible filters and standardized actions.')"
    >
        @if (session('status') || $errors->any())
            <div class="p-5 pb-0 space-y-4">
                @if (session('status'))
                    <div class="kt-alert kt-alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="kt-alert kt-alert-destructive">
                        {{ $errors->first() }}
                    </div>
                @endif
            </div>
        @endif

        <livewire:tenant-table />
    </x-tables.panel>
@endsection
