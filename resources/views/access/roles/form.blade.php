@extends('layouts.metronic.app')

@section('title', $mode === 'create' ? __('Create role') : __('Edit role'))

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title flex-column align-items-start">
                <h3 class="fw-bold mb-1">
                    {{ $mode === 'create' ? __('New company role') : __('Edit role: :name', ['name' => $role->name]) }}
                </h3>
                <span class="text-muted fw-semibold fs-7">
                    {{ __('Permissions are applied to the active company and distinguish entity access from content access.') }}
                </span>
            </div>

            <div class="card-toolbar">
                <a class="btn btn-light-primary btn-sm" href="{{ route('access.roles.index') }}">
                    <i class="ki-duotone ki-left fs-3"></i>
                    {{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="card-body pt-0">
            <form
                method="POST"
                action="{{ $mode === 'create' ? route('access.roles.store') : route('access.roles.update', ['role' => $role]) }}"
            >
                @csrf
                @if ($mode === 'edit')
                    @method('PUT')
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger mb-6">{{ $errors->first() }}</div>
                @endif

                <div class="mb-8" style="max-width: 720px;">
                    <label class="form-label fw-semibold fs-6" for="name">{{ __('Role name') }}</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        class="form-control form-control-solid"
                        value="{{ old('name', $role?->name) }}"
                        placeholder="{{ __('Example: game_editor') }}"
                        required
                    >
                    <div class="form-text">{{ __('This name will be unique within the company.') }}</div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-5">
                    <h4 class="fw-bold mb-0">{{ __('Permissions') }}</h4>
                    <button
                        type="button"
                        class="btn btn-light-primary btn-sm"
                        onclick="document.querySelectorAll('input[name=&quot;permissions[]&quot;]').forEach((el) => { el.checked = true; });"
                    >
                        {{ __('Select all') }}
                    </button>
                </div>

                @foreach ($permissionGroups as $group => $permissions)
                    <div class="border rounded p-5 mb-5">
                        <h5 class="fw-bold mb-4">{{ $group }}</h5>

                        <div class="row g-4">
                            @foreach ($permissions as $permission)
                                @php
                                    $checked = in_array($permission['name'], old('permissions', $selectedPermissions), true);
                                @endphp
                                <div class="col-12 col-lg-6">
                                    <label class="d-flex align-items-start gap-3 border rounded p-4 h-100 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            class="form-check-input mt-1"
                                            name="permissions[]"
                                            value="{{ $permission['name'] }}"
                                            @checked($checked)
                                        >

                                        <span>
                                            <span class="d-block fw-semibold text-gray-900">{{ $permission['label'] }}</span>
                                            <span class="d-block text-muted fs-7">{{ $permission['description'] }}</span>
                                            <span class="d-block text-gray-600 fs-8 mt-1">{{ $permission['name'] }}</span>
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="pt-2">
                    <button class="btn btn-primary" type="submit">
                        {{ $mode === 'create' ? __('Create role') : __('Save changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
