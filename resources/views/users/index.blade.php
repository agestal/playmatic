@extends('layouts.metronic.app')

@section('title', 'Usuarios')

@section('content')
    <x-tables.panel
        title="Usuarios"
        description="Gestion de usuarios de la empresa activa con filtros estandarizados y acciones rapidas."
    >
        <livewire:user-table />
    </x-tables.panel>
@endsection
