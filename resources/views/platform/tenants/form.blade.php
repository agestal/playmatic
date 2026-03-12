@extends('layouts.metronic.app')

@section('title', $mode === 'create' ? __('New tenant') : __('Edit tenant'))

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title flex-column align-items-start">
                <h3 class="fw-bold mb-1">{{ $mode === 'create' ? __('New tenant') : __('Edit tenant') }}</h3>
                <span class="text-muted fw-semibold fs-7">
                    {{ __('Tenant base configuration: name, slug, primary domain, owner, branding, and enabled games.') }}
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

            @php
                $selectedGameIdsForForm = collect(old('game_ids', $selectedGameIds ?? []))
                    ->map(static fn ($id): string => (string) $id)
                    ->all();
            @endphp

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
                    <div class="form-text">{{ __('The :role role and active status will be assigned in this tenant.', ['role' => 'admin']) }}</div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold fs-6" for="logo">{{ __('Logo URL (optional)') }}</label>
                    <input
                        id="logo"
                        type="text"
                        name="logo"
                        class="form-control form-control-solid"
                        value="{{ old('logo', $tenant?->logo) }}"
                        placeholder="https://cdn.example.com/tenant-logo.svg"
                        maxlength="2048"
                    >
                    <div class="form-text">{{ __('Leave empty if you do not want a logo yet.') }}</div>
                </div>

                <div class="col-12 col-lg-6">
                    <label class="form-label fw-semibold fs-6" for="primary_color">{{ __('Primary color (hex)') }}</label>
                    <input
                        id="primary_color"
                        type="text"
                        name="primary_color"
                        class="form-control form-control-solid"
                        value="{{ old('primary_color', $tenant?->primary_color) }}"
                        placeholder="#0D6EFD"
                        maxlength="7"
                        pattern="^#[0-9A-Fa-f]{6}$"
                        data-kt-color-picker="true"
                        data-kt-color-picker-input-mode="true"
                        data-kt-color-picker-lock-opacity="true"
                        data-kt-color-picker-default-representation="HEX"
                    >
                    <div class="form-text">{{ __('Use HEX format, for example :example.', ['example' => '#0D6EFD']) }}</div>
                </div>

                <div class="col-12 col-lg-6">
                    <label class="form-label fw-semibold fs-6" for="secondary_color">{{ __('Secondary color (hex)') }}</label>
                    <input
                        id="secondary_color"
                        type="text"
                        name="secondary_color"
                        class="form-control form-control-solid"
                        value="{{ old('secondary_color', $tenant?->secondary_color) }}"
                        placeholder="#20C997"
                        maxlength="7"
                        pattern="^#[0-9A-Fa-f]{6}$"
                        data-kt-color-picker="true"
                        data-kt-color-picker-input-mode="true"
                        data-kt-color-picker-lock-opacity="true"
                        data-kt-color-picker-default-representation="HEX"
                    >
                    <div class="form-text">{{ __('Use HEX format, for example :example.', ['example' => '#20C997']) }}</div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold fs-6">{{ __('Games') }}</label>
                    <input type="hidden" name="game_ids_present" value="1">

                    <div class="row g-3">
                        @forelse ($gameOptions as $gameOption)
                            <div class="col-12 col-lg-6">
                                <label class="d-flex align-items-start gap-3 border rounded p-4 h-100 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        class="form-check-input mt-1"
                                        name="game_ids[]"
                                        value="{{ $gameOption['id'] }}"
                                        @checked(in_array((string) $gameOption['id'], $selectedGameIdsForForm, true))
                                    >

                                    <span>
                                        <span class="d-block fw-semibold text-gray-900">{{ $gameOption['name'] }}</span>
                                        <span class="d-block text-muted fs-8">{{ $gameOption['slug'] }}</span>
                                    </span>
                                </label>
                            </div>
                        @empty
                            <div class="col-12">
                                <span class="text-muted">{{ __('No games available.') }}</span>
                            </div>
                        @endforelse
                    </div>

                    <div class="form-text">{{ __('Enable or disable game access for this tenant.') }}</div>
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

@push('scripts')
    <script>
        (function () {
            const form = document.querySelector('form[action*="platform.tenants"]') || document.querySelector('form');
            const colorInputs = [
                document.getElementById('primary_color'),
                document.getElementById('secondary_color'),
            ].filter(Boolean);

            if (!form || colorInputs.length === 0) {
                return;
            }

            window.KTColorPicker?.createInstances?.();

            const colorToHex = (value) => {
                const color = String(value || '').trim();

                if (color === '') {
                    return '';
                }

                const shortHexMatch = color.match(/^#([0-9a-fA-F]{3})$/);
                if (shortHexMatch) {
                    const expanded = shortHexMatch[1].split('').map((char) => char + char).join('');
                    return `#${expanded.toUpperCase()}`;
                }

                const hexMatch = color.match(/^#([0-9a-fA-F]{6})$/);
                if (hexMatch) {
                    return `#${hexMatch[1].toUpperCase()}`;
                }

                const hexaMatch = color.match(/^#([0-9a-fA-F]{8})$/);
                if (hexaMatch) {
                    return `#${hexaMatch[1].slice(0, 6).toUpperCase()}`;
                }

                const canvas = document.createElement('canvas');
                canvas.width = 1;
                canvas.height = 1;
                const context = canvas.getContext('2d');

                if (!context) {
                    return null;
                }

                context.fillStyle = '#000000';
                context.fillStyle = color;

                const normalized = context.fillStyle;
                const rgbMatch = normalized.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/i);

                if (!rgbMatch) {
                    return null;
                }

                const hex = [rgbMatch[1], rgbMatch[2], rgbMatch[3]]
                    .map((part) => Number(part).toString(16).padStart(2, '0'))
                    .join('')
                    .toUpperCase();

                return `#${hex}`;
            };

            const normalizeField = (input) => {
                const hex = colorToHex(input.value);

                if (hex !== null) {
                    input.value = hex;
                }
            };

            const initNativeColorFallback = (input) => {
                if (!input || input.dataset.nativeColorFallback === 'true') {
                    return;
                }

                const nativePicker = document.createElement('input');
                nativePicker.type = 'color';
                nativePicker.className = 'form-control form-control-color';
                nativePicker.style.width = '3rem';
                nativePicker.style.minWidth = '3rem';
                nativePicker.style.padding = '0.25rem';
                nativePicker.style.cursor = 'pointer';
                nativePicker.title = 'Open color picker';

                const syncFromText = () => {
                    const hex = colorToHex(input.value);
                    if (hex) {
                        nativePicker.value = hex;
                    }
                };

                const syncFromPicker = () => {
                    input.value = nativePicker.value.toUpperCase();
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                };

                nativePicker.addEventListener('input', syncFromPicker);
                input.addEventListener('input', syncFromText);
                input.addEventListener('change', syncFromText);
                syncFromText();

                input.insertAdjacentElement('afterend', nativePicker);
                input.dataset.nativeColorFallback = 'true';
            };

            colorInputs.forEach((input) => {
                input.addEventListener('input', () => normalizeField(input));
                input.addEventListener('change', () => normalizeField(input));
                normalizeField(input);
            });

            document.addEventListener('kt.color-picker.change', (event) => {
                const element = event?.detail?.element;
                if (!element || !colorInputs.includes(element)) {
                    return;
                }

                normalizeField(element);
            });

            document.addEventListener('kt.color-picker.save', (event) => {
                const element = event?.detail?.element;
                if (!element || !colorInputs.includes(element)) {
                    return;
                }

                normalizeField(element);
            });

            const hasKtPicker = typeof window.KTColorPicker?.getInstance === 'function';
            const ktPickerReady = hasKtPicker && colorInputs.every((input) => window.KTColorPicker.getInstance(input));
            if (!ktPickerReady) {
                colorInputs.forEach((input) => initNativeColorFallback(input));
            }

            form.addEventListener('submit', () => {
                colorInputs.forEach((input) => normalizeField(input));
            });
        })();
    </script>
@endpush
