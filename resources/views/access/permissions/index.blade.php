@extends('layouts.metronic.app')

@section('title', 'Permisos')

@section('content')
    <x-tables.panel
        title="Permisos"
        description="Catalogo de permisos disponibles con filtros colapsables y acciones estandarizadas."
    >
        <livewire:permission-table />
    </x-tables.panel>
@endsection
