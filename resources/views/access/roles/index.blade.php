@extends('layouts.metronic.app')

@section('title', 'Roles y permisos')

@section('content')
    <x-tables.panel
        title="Roles y permisos"
        description="Configura roles por empresa y define exactamente a que entidades y contenido puede acceder cada perfil."
    >
        <x-slot:actions>
            <a href="{{ route('access.roles.create') }}" class="kt-btn kt-btn-primary kt-btn-sm">
                <i class="ki-filled ki-plus"></i>
                Nuevo rol
            </a>
        </x-slot:actions>

        <div class="p-5 space-y-4">
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

            <div class="rounded-lg border border-gray-200 overflow-x-auto">
                <table class="kt-table kt-table-border w-full">
                    <thead>
                        <tr>
                            <th>Rol</th>
                            <th>Permisos</th>
                            <th>Usuarios</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $role)
                            <tr>
                                <td class="font-medium text-gray-900">{{ $role->name }}</td>
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($role->permissions->take(4) as $permission)
                                            <span class="kt-badge kt-badge-sm kt-badge-outline">{{ $permission->name }}</span>
                                        @endforeach
                                        @if ($role->permissions_count > 4)
                                            <span class="kt-badge kt-badge-sm kt-badge-light">+{{ $role->permissions_count - 4 }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="kt-badge kt-badge-sm kt-badge-light">{{ $role->tenant_users_count }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="inline-flex items-center gap-1">
                                        <a
                                            href="{{ route('access.roles.edit', $role) }}"
                                            class="kt-btn kt-btn-sm kt-btn-icon kt-btn-light"
                                            title="Editar rol"
                                        >
                                            <i class="ki-outline ki-notepad-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('access.roles.destroy', $role) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="kt-btn kt-btn-sm kt-btn-icon kt-btn-light-danger"
                                                onclick="return confirm('¿Eliminar este rol?')"
                                                title="Eliminar rol"
                                            >
                                                <i class="ki-outline ki-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-sm text-gray-500 py-8">
                                    No hay roles creados para la empresa {{ $tenant->name }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-tables.panel>
@endsection
