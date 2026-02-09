@extends('layouts.metronic.app')

@section('title', $mode === 'create' ? 'Crear rol' : 'Editar rol')

@section('content')
    <x-tables.panel
        :title="$mode === 'create' ? 'Nuevo rol de empresa' : 'Editar rol: '.$role->name"
        description="Los permisos se aplican en la empresa activa y distinguen entre acceso a entidad y acceso a contenido."
    >
        <x-slot:actions>
            <a href="{{ route('access.roles.index') }}" class="kt-btn kt-btn-light kt-btn-sm">
                <i class="ki-filled ki-left"></i>
                Volver
            </a>
        </x-slot:actions>

        <form
            method="POST"
            action="{{ $mode === 'create' ? route('access.roles.store') : route('access.roles.update', $role) }}"
            class="p-5 space-y-5"
        >
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            @if ($errors->any())
                <div class="kt-alert kt-alert-destructive">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="max-w-xl space-y-2">
                <label for="name" class="text-sm font-medium text-gray-700">Nombre del rol</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    class="kt-input"
                    value="{{ old('name', $role?->name) }}"
                    placeholder="Ej: editor_juegos"
                    required
                >
                <p class="text-xs text-gray-500">
                    Este nombre sera unico dentro de la empresa.
                </p>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between gap-2">
                    <h4 class="text-sm font-semibold text-gray-900">Permisos</h4>
                    <button
                        type="button"
                        class="kt-btn kt-btn-light kt-btn-xs"
                        onclick="document.querySelectorAll('input[name=&quot;permissions[]&quot;]').forEach((el) => { el.checked = true; });"
                    >
                        Marcar todo
                    </button>
                </div>

                @foreach ($permissionGroups as $group => $permissions)
                    <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                        <h5 class="text-sm font-semibold text-gray-800">{{ $group }}</h5>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                            @foreach ($permissions as $permission)
                                @php
                                    $checked = in_array($permission['name'], old('permissions', $selectedPermissions), true);
                                @endphp
                                <label class="flex items-start gap-2 rounded-md border border-gray-200 p-3 hover:bg-gray-50">
                                    <input
                                        type="checkbox"
                                        name="permissions[]"
                                        value="{{ $permission['name'] }}"
                                        @checked($checked)
                                        class="kt-checkbox mt-0.5"
                                    >
                                    <span class="space-y-1">
                                        <span class="block text-sm font-medium text-gray-900">{{ $permission['label'] }}</span>
                                        <span class="block text-xs text-gray-500">{{ $permission['description'] }}</span>
                                        <span class="block text-[11px] text-gray-400">{{ $permission['name'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-2">
                <button type="submit" class="kt-btn kt-btn-primary">
                    {{ $mode === 'create' ? 'Crear rol' : 'Guardar cambios' }}
                </button>
            </div>
        </form>
    </x-tables.panel>
@endsection
