@extends('layouts.metronic.app')

@section('title', 'Usuarios')

@section('content')
    <x-tables.panel
        title="Usuarios"
        description="Listado de usuarios de la empresa activa con filtros colapsables y acciones estandarizadas."
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

        <livewire:user-table />
    </x-tables.panel>
@endsection
