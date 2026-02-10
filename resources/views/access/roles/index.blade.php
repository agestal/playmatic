@extends('layouts.metronic.app')

@section('title', __('Roles'))

@section('content')
    <x-tables.panel
        :title="__('Roles')"
        :description="__('Manage roles for the active company with standardized filters and actions.')"
    >
        @if (filled(request('permission')) || session('status') || $errors->any())
            <div class="p-5 pb-0 space-y-4">
                @if (filled(request('permission')))
                    <div class="kt-alert kt-alert-primary">
                        {{ __('Filtered by permission:') }} <span class="font-semibold">{{ request('permission') }}</span>
                    </div>
                @endif

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

        <livewire:role-table />
    </x-tables.panel>
@endsection
