@extends('layouts.metronic.app')

@section('title', $mode === 'create' ? __('New tenant') : __('Edit tenant'))

@section('content')
    <div class="kt-card pm-surface-card overflow-hidden">
        <div class="kt-card-header table-panel-header">
            <div class="flex flex-col gap-1">
                <h3 class="kt-card-title text-base font-semibold text-mono">
                    {{ $mode === 'create' ? __('New tenant') : __('Edit tenant') }}
                </h3>
                <p class="text-sm text-secondary-foreground">
                    {{ __('Tenant base configuration: name, slug, primary domain, and owner.') }}
                </p>
            </div>

            <div class="kt-card-toolbar">
                <a href="{{ route('platform.tenants.index') }}" class="kt-btn kt-btn-sm kt-btn-light">
                    {{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="kt-card-content p-0">
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
                    action="{{ $mode === 'create' ? route('platform.tenants.store') : route('platform.tenants.update', ['tenant' => $tenant]) }}"
                    class="grid grid-cols-1 lg:grid-cols-2 gap-4"
                >
                    @csrf
                    @if ($mode === 'edit')
                        @method('PUT')
                    @endif

                    <div class="space-y-1">
                        <label for="name" class="text-xs text-secondary-foreground">{{ __('Display name') }}</label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            class="kt-input"
                            value="{{ old('name', $tenant?->name) }}"
                            placeholder="{{ __('Acme Corp') }}"
                            maxlength="120"
                            required
                        >
                    </div>

                    <div class="space-y-1">
                        <label for="slug" class="text-xs text-secondary-foreground">{{ __('Slug (unique)') }}</label>
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
                        <label for="primary_domain" class="text-xs text-secondary-foreground">{{ __('Primary domain') }}</label>
                        <input
                            id="primary_domain"
                            type="text"
                            name="primary_domain"
                            class="kt-input"
                            value="{{ old('primary_domain', $primaryDomain) }}"
                            placeholder="acme.playmatic.local"
                            required
                        >
                        <p class="text-xs text-secondary-foreground">{{ __('You can enter only the host or a full URL; it will be normalized automatically.') }}</p>
                    </div>

                    <div class="space-y-1">
                        <label for="owner_email" class="text-xs text-secondary-foreground">{{ __('Owner email (existing user)') }}</label>
                        <input
                            id="owner_email"
                            type="email"
                            name="owner_email"
                            class="kt-input"
                            value="{{ old('owner_email', $ownerEmail) }}"
                            placeholder="owner@empresa.com"
                            required
                        >
                        <p class="text-xs text-secondary-foreground">{{ __('The :role role and active status will be assigned in this tenant.', ['role' => 'tenant_admin']) }}</p>
                    </div>

                    <div class="lg:col-span-2 flex items-center gap-2">
                        <button type="submit" class="kt-btn kt-btn-primary kt-btn-sm">
                            {{ $mode === 'create' ? __('Create tenant') : __('Save changes') }}
                        </button>

                        @if ($mode === 'edit')
                            <a href="{{ route('platform.tenants.create') }}" class="kt-btn kt-btn-sm kt-btn-light">
                                {{ __('New tenant') }}
                            </a>
                        @endif
                    </div>
                </form>

                @if ($tenant)
                    <div class="rounded-lg border border-border p-4">
                        <h4 class="text-sm font-semibold text-mono mb-2">{{ __('Linked domains') }}</h4>
                        <div class="flex flex-wrap gap-2">
                            @forelse ($tenant->domains as $domain)
                                <span class="kt-badge kt-badge-sm {{ $domain->is_primary ? 'kt-badge-light-primary' : 'kt-badge-light' }}">
                                    {{ $domain->domain }}
                                    @if ($domain->is_primary)
                                        ({{ __('primary') }})
                                    @endif
                                </span>
                            @empty
                                <span class="text-sm text-secondary-foreground">{{ __('No domains configured.') }}</span>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
