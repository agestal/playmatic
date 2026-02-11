@extends('layouts.metronic.app')

@section('title', $mode === 'create' ? __('New tenant') : __('Edit tenant'))

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title flex-column align-items-start">
                <h3 class="fw-bold mb-1">{{ $mode === 'create' ? __('New tenant') : __('Edit tenant') }}</h3>
                <span class="text-muted fw-semibold fs-7">
                    {{ __('Tenant base configuration: name, slug, primary domain, and owner.') }}
                </span>
            </div>

            <div class="card-toolbar">
                <a class="btn btn-light-primary btn-sm" href="{{ route('platform.tenants.index') }}">{{ __('Back') }}</a>
            </div>
        </div>

        <div class="card-body pt-0">
            @if (session('status'))
                <div class="alert alert-success mb-6">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-6">{{ $errors->first() }}</div>
            @endif

            <form
                method="POST"
                action="{{ $mode === 'create' ? route('platform.tenants.store') : route('platform.tenants.update', ['tenant' => $tenant]) }}"
                class="row g-5"
            >
                @csrf
                @if ($mode === 'edit')
                    @method('PUT')
                @endif

                <div class="col-12 col-lg-6">
                    <label class="form-label fw-semibold fs-6" for="name">{{ __('Display name') }}</label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        class="form-control form-control-solid"
                        value="{{ old('name', $tenant?->name) }}"
                        placeholder="{{ __('Acme Corp') }}"
                        maxlength="120"
                        required
                    >
                </div>

                <div class="col-12 col-lg-6">
                    <label class="form-label fw-semibold fs-6" for="slug">{{ __('Slug (unique)') }}</label>
                    <input
                        id="slug"
                        type="text"
                        name="slug"
                        class="form-control form-control-solid"
                        value="{{ old('slug', $tenant?->slug) }}"
                        placeholder="acme-corp"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="col-12 col-lg-6">
                    <label class="form-label fw-semibold fs-6" for="primary_domain">{{ __('Primary domain') }}</label>
                    <input
                        id="primary_domain"
                        type="text"
                        name="primary_domain"
                        class="form-control form-control-solid"
                        value="{{ old('primary_domain', $primaryDomain) }}"
                        placeholder="acme.playmatic.local"
                        required
                    >
                    <div class="form-text">{{ __('You can enter only the host or a full URL; it will be normalized automatically.') }}</div>
                </div>

                <div class="col-12 col-lg-6">
                    <label class="form-label fw-semibold fs-6" for="owner_email">{{ __('Owner email (existing user)') }}</label>
                    <input
                        id="owner_email"
                        type="email"
                        name="owner_email"
                        class="form-control form-control-solid"
                        value="{{ old('owner_email', $ownerEmail) }}"
                        placeholder="owner@empresa.com"
                        required
                    >
                    <div class="form-text">{{ __('The :role role and active status will be assigned in this tenant.', ['role' => 'tenant_admin']) }}</div>
                </div>

                <div class="col-12 d-flex align-items-center gap-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        {{ $mode === 'create' ? __('Create tenant') : __('Save changes') }}
                    </button>

                    @if ($mode === 'edit')
                        <a href="{{ route('platform.tenants.create') }}" class="btn btn-light btn-sm">{{ __('New tenant') }}</a>
                    @endif
                </div>
            </form>

            @if ($tenant)
                <div class="separator my-8"></div>
                <div>
                    <h4 class="fw-bold mb-3">{{ __('Linked domains') }}</h4>
                    <div class="d-flex flex-wrap gap-2">
                        @forelse ($tenant->domains as $domain)
                            <span class="badge {{ $domain->is_primary ? 'badge-light-primary' : 'badge-light' }} fw-semibold">
                                {{ $domain->domain }}
                                @if ($domain->is_primary)
                                    ({{ __('primary') }})
                                @endif
                            </span>
                        @empty
                            <span class="text-muted">{{ __('No domains configured.') }}</span>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
