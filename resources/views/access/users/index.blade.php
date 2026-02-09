@extends('layouts.metronic.app')

@section('title', 'Accesos de usuarios')

@section('content')
    <x-tables.panel
        title="Asignacion de roles a usuarios"
        description="Gestiona el rol de cada usuario dentro de la empresa activa. El acceso final siempre queda limitado al dominio y empresa actual."
    >
        <div class="p-5 space-y-5">
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

            <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                <h4 class="text-sm font-semibold text-gray-900">Agregar o actualizar acceso</h4>

                <form method="POST" action="{{ route('access.users.store') }}" class="grid grid-cols-1 lg:grid-cols-4 gap-3">
                    @csrf
                    <div class="space-y-1 lg:col-span-2">
                        <label for="email" class="text-xs text-gray-600">Email del usuario</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            class="kt-input"
                            value="{{ old('email') }}"
                            placeholder="usuario@empresa.com"
                            required
                        >
                    </div>

                    <div class="space-y-1">
                        <label for="role_id" class="text-xs text-gray-600">Rol</label>
                        <select id="role_id" name="role_id" class="kt-select" required>
                            <option value="">Selecciona rol</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected((string) old('role_id') === (string) $role->id)>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label for="status" class="text-xs text-gray-600">Estado</label>
                        <select id="status" name="status" class="kt-select" required>
                            <option value="active" @selected(old('status', 'active') === 'active')>Activo</option>
                            <option value="disabled" @selected(old('status') === 'disabled')>Deshabilitado</option>
                        </select>
                    </div>

                    <div class="lg:col-span-4">
                        <button type="submit" class="kt-btn kt-btn-primary kt-btn-sm">
                            Guardar acceso
                        </button>
                    </div>
                </form>
            </div>

            <div class="rounded-lg border border-gray-200 overflow-x-auto">
                <table class="kt-table kt-table-border w-full">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($memberships as $membership)
                            <tr>
                                <td class="font-medium text-gray-900">{{ $membership->user->name }}</td>
                                <td>{{ $membership->user->email }}</td>
                                <td colspan="3">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <form
                                            method="POST"
                                            action="{{ route('access.users.update', $membership) }}"
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            @csrf
                                            @method('PUT')

                                            <select name="role_id" class="kt-select kt-select-sm min-w-[180px]" required>
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->id }}" @selected($membership->role_id === $role->id)>
                                                        {{ $role->name }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <select name="status" class="kt-select kt-select-sm min-w-[140px]" required>
                                                <option value="active" @selected($membership->status === 'active')>Activo</option>
                                                <option value="disabled" @selected($membership->status === 'disabled')>Deshabilitado</option>
                                            </select>

                                            <button type="submit" class="kt-btn kt-btn-sm kt-btn-light-primary">
                                                Guardar
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('access.users.destroy', $membership) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="kt-btn kt-btn-sm kt-btn-icon kt-btn-light-danger"
                                                onclick="return confirm('¿Eliminar este acceso?')"
                                                title="Eliminar acceso"
                                            >
                                                <i class="ki-outline ki-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-sm text-gray-500 py-8">
                                    No hay usuarios vinculados a la empresa {{ $tenant->name }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $memberships->links() }}
            </div>
        </div>
    </x-tables.panel>
@endsection
