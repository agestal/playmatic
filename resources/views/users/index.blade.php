@extends('layouts.metronic.app')

@section('title', 'Users')

@section('content')
<x-tables.panel title="User Management" description="Gestión de usuarios, búsqueda y acciones rápidas.">
    <x-slot:actions>
        <a href="{{ route('access.users.index') }}" class="kt-btn kt-btn-primary kt-btn-sm">
            <i class="ki-filled ki-plus"></i>
            Asignar acceso
        </a>
    </x-slot:actions>

    <livewire:user-table />
</x-tables.panel>
@endsection
