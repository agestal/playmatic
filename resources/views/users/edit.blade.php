@extends('layouts.metronic.app')

@section('title', __('Edit user'))
@section('page_title', __('Edit user'))

@section('content')
    @if (session('status'))
        <div class="alert alert-success d-flex align-items-center p-5 mb-8">
            <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
            <div class="d-flex flex-column">
                <span>{{ session('status') }}</span>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-center p-5 mb-8">
            <i class="ki-duotone ki-information fs-2hx text-danger me-4">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
            <div class="d-flex flex-column">
                <span>{{ $errors->first() }}</span>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title flex-column align-items-start">
                <h3 class="fw-bold mb-1">{{ $user->name }}</h3>
                <span class="text-muted fw-semibold fs-7">{{ $user->email }}</span>
            </div>

            <div class="card-toolbar">
                <a class="btn btn-light-primary btn-sm" href="{{ route('users.index') }}">
                    <i class="ki-duotone ki-left fs-3"></i>
                    {{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="card-body pt-0">
            <form method="POST" action="{{ route('users.update', ['tenantUser' => $tenantUser->id]) }}">
                @csrf
                @method('PUT')

                <div class="row g-6">
                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold" for="role_id">{{ __('Role') }}</label>
                        <select class="form-select form-select-solid" id="role_id" name="role_id" required>
                            <option value="">{{ __('Select role') }}</option>
                            @foreach ($roleChoices as $roleChoice)
                                <option
                                    @selected((string) old('role_id', $tenantUser->role_id) === (string) $roleChoice->id)
                                    value="{{ $roleChoice->id }}"
                                >
                                    {{ $roleChoice->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold" for="status">{{ __('Status') }}</label>
                        <select class="form-select form-select-solid" id="status" name="status" required>
                            <option @selected(old('status', $tenantUser->status) === 'active') value="active">{{ __('Active') }}</option>
                            <option @selected(old('status', $tenantUser->status) === 'disabled') value="disabled">{{ __('Disabled') }}</option>
                        </select>
                    </div>
                </div>

                <div class="pt-8">
                    <button class="btn btn-primary" type="submit">{{ __('Save changes') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-8">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="fw-bold mb-0">{{ __('Password') }}</h3>
            </div>
        </div>

        <div class="card-body pt-0">
            <form method="POST" action="{{ route('users.password.update', ['tenantUser' => $tenantUser->id]) }}">
                @csrf
                @method('PUT')

                <div class="row g-6">
                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold" for="password">{{ __('Password') }}</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            class="form-control form-control-solid"
                            required
                        >
                    </div>

                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold" for="password_confirmation">{{ __('Confirm Password') }}</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            class="form-control form-control-solid"
                            required
                        >
                    </div>
                </div>

                <div class="pt-8">
                    <button class="btn btn-primary" type="submit">{{ __('Save changes') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
