@extends('layouts.metronic.app')

@section('title', $mode === 'create' ? __('Create game') : __('Edit game'))
@section('page_title', $mode === 'create' ? __('Create game') : __('Edit game'))

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title flex-column align-items-start">
                <h3 class="fw-bold mb-1">
                    {{ $mode === 'create' ? __('New game') : __('Edit game: :name', ['name' => $game->name]) }}
                </h3>
                <span class="text-muted fw-semibold fs-7">{{ __('Game entity is global; tenant visibility is managed through assignments.') }}</span>
            </div>

            <div class="card-toolbar">
                <a class="btn btn-light-primary btn-sm" href="{{ route('games.index') }}">
                    <i class="ki-duotone ki-left fs-3"></i>
                    {{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="card-body pt-0">
            <form method="POST" action="{{ $mode === 'create' ? route('games.store') : route('games.update', ['game' => $game]) }}">
                @csrf
                @if ($mode === 'edit')
                    @method('PUT')
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger mb-6">{{ $errors->first() }}</div>
                @endif

                <div class="row g-6">
                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold" for="name">{{ __('Name') }}</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            class="form-control form-control-solid"
                            value="{{ old('name', $game?->name) }}"
                            required
                        >
                    </div>

                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold" for="slug">{{ __('Slug') }}</label>
                        <input
                            id="slug"
                            name="slug"
                            type="text"
                            class="form-control form-control-solid"
                            value="{{ old('slug', $game?->slug) }}"
                            placeholder="trivial"
                            required
                        >
                    </div>

                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold" for="game_type">{{ __('Type') }}</label>
                        <input
                            id="game_type"
                            name="game_type"
                            type="text"
                            class="form-control form-control-solid"
                            value="{{ old('game_type', $game?->game_type) }}"
                            placeholder="quiz"
                            required
                        >
                    </div>

                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold" for="is_active">{{ __('Status') }}</label>
                        <div class="form-check form-switch form-check-custom form-check-solid mt-2">
                            <input type="hidden" name="is_active" value="0">
                            <input
                                class="form-check-input"
                                id="is_active"
                                name="is_active"
                                type="checkbox"
                                value="1"
                                @checked((bool) old('is_active', $game?->is_active ?? true))
                            >
                            <label class="form-check-label" for="is_active">{{ __('Game active') }}</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold" for="description">{{ __('Description') }}</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="3"
                            class="form-control form-control-solid"
                        >{{ old('description', $game?->description) }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold" for="config_json">{{ __('Config (JSON)') }}</label>
                        <textarea
                            id="config_json"
                            name="config_json"
                            rows="6"
                            class="form-control form-control-solid font-monospace"
                            placeholder='{"key":"value"}'
                        >{{ old('config_json', $game ? json_encode($game->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                        <div class="form-text">{{ __('Optional JSON object with game-specific settings.') }}</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">{{ __('Visible in tenants') }}</label>

                        @if ($isSuperadmin)
                            <select name="tenant_ids[]" class="form-select form-select-solid" multiple size="6" required>
                                @foreach ($tenantOptions as $tenantOption)
                                    <option
                                        value="{{ $tenantOption['id'] }}"
                                        @selected(in_array((int) $tenantOption['id'], old('tenant_ids', $selectedTenantIds), true))
                                    >
                                        {{ $tenantOption['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">{{ __('Hold Ctrl/Cmd to select multiple tenants.') }}</div>
                        @else
                            @foreach ($selectedTenantIds as $tenantId)
                                <input type="hidden" name="tenant_ids[]" value="{{ $tenantId }}">
                            @endforeach

                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($tenantOptions as $tenantOption)
                                    <span class="badge badge-light-primary">{{ $tenantOption['name'] }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="pt-8">
                    <button class="btn btn-primary" type="submit">
                        {{ $mode === 'create' ? __('Create game') : __('Save changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
