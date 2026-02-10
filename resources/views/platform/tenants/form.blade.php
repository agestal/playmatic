@extends('layouts.metronic.app')

@section('title', $mode === 'create' ? 'Nuevo tenant' : 'Editar tenant')

@section('content')
    <x-tables.panel
        :title="$mode === 'create' ? 'Nuevo tenant' : 'Editar tenant'"
        description="Configuracion base del tenant: nombre, slug, dominio primario y owner."
    >
        <x-slot:actions>
            <a href="{{ route('platform.tenants.index') }}" class="kt-btn kt-btn-sm kt-btn-light">
                Volver
            </a>
        </x-slot:actions>

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

            <form
                method="POST"
                action="{{ $mode === 'create' ? route('platform.tenants.store') : route('platform.tenants.update', $tenant) }}"
                class="grid grid-cols-1 lg:grid-cols-2 gap-4"
            >
                @csrf
                @if ($mode === 'edit')
                    @method('PUT')
                @endif

                <div class="space-y-1">
                    <label for="name" class="text-xs text-gray-600">Nombre visible</label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        class="kt-input"
                        value="{{ old('name', $tenant?->name) }}"
                        placeholder="Acme Corp"
                        maxlength="120"
                        required
                    >
                </div>

                <div class="space-y-1">
                    <label for="slug" class="text-xs text-gray-600">Slug (unico)</label>
                    <input
                        id="slug"
                        type="text"
                        name="slug"
                        class="kt-input"
                        value="{{ old('slug', $tenant?->slug) }}"
                        placeholder="acme-corp"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="space-y-1">
                    <label for="primary_domain" class="text-xs text-gray-600">Dominio primario</label>
                    <input
                        id="primary_domain"
                        type="text"
                        name="primary_domain"
                        class="kt-input"
                        value="{{ old('primary_domain', $primaryDomain) }}"
                        placeholder="acme.playmatic.local"
                        required
                    >
                    <p class="text-xs text-gray-500">Puedes poner solo host o URL completa; se normaliza automaticamente.</p>
                </div>

                <div class="space-y-1">
                    <label for="owner_email" class="text-xs text-gray-600">Owner email (usuario existente)</label>
                    <input
                        id="owner_email"
                        type="email"
                        name="owner_email"
                        class="kt-input"
                        value="{{ old('owner_email', $ownerEmail) }}"
                        placeholder="owner@empresa.com"
                        required
                    >
                    <p class="text-xs text-gray-500">Se asignara rol <code>tenant_admin</code> y estado activo en este tenant.</p>
                </div>

                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="submit" class="kt-btn kt-btn-primary kt-btn-sm">
                        {{ $mode === 'create' ? 'Crear tenant' : 'Guardar cambios' }}
                    </button>

                    @if ($mode === 'edit')
                        <a href="{{ route('platform.tenants.create') }}" class="kt-btn kt-btn-sm kt-btn-light">
                            Nuevo tenant
                        </a>
                    @endif
                </div>
            </form>

            @if ($tenant)
                <div class="rounded-lg border border-gray-200 p-4">
                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Dominios vinculados</h4>
                    <div class="flex flex-wrap gap-2">
                        @forelse ($tenant->domains as $domain)
                            <span class="kt-badge kt-badge-sm {{ $domain->is_primary ? 'kt-badge-light-primary' : 'kt-badge-light' }}">
                                {{ $domain->domain }}
                                @if ($domain->is_primary)
                                    (primary)
                                @endif
                            </span>
                        @empty
                            <span class="text-sm text-gray-500">Sin dominios configurados.</span>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    </x-tables.panel>
@endsection
