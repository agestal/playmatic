@extends('layouts.metronic.app')

@section('title', $mode === 'create' ? __('Create quiz question') : __('Edit quiz question'))
@section('page_title', $mode === 'create' ? __('Create quiz question') : __('Edit quiz question'))

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title flex-column align-items-start">
                <h3 class="fw-bold mb-1">
                    {{ $mode === 'create' ? __('New quiz question') : __('Edit quiz question #:id', ['id' => $question->id]) }}
                </h3>
                <span class="text-muted fw-semibold fs-7">{{ __('Each question belongs to one tenant and can have multiple answers.') }}</span>
            </div>

            <div class="card-toolbar">
                <a class="btn btn-light-primary btn-sm" href="{{ route('games.quiz.questions.index') }}">
                    <i class="ki-duotone ki-left fs-3"></i>
                    {{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="card-body pt-0">
            <form method="POST" action="{{ $mode === 'create' ? route('games.quiz.questions.store') : route('games.quiz.questions.update', ['question' => $question->id]) }}">
                @csrf
                @if ($mode === 'edit')
                    @method('PUT')
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger mb-6">{{ $errors->first() }}</div>
                @endif

                <div class="row g-6">
                    @if ($isSuperadmin)
                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-semibold" for="tenant_id">{{ __('Tenant') }}</label>
                            @if ($mode === 'create')
                                <select id="tenant_id" name="tenant_id" class="form-select form-select-solid" required>
                                    <option value="">{{ __('Select tenant') }}</option>
                                    @foreach ($tenantOptions as $tenantOption)
                                        <option
                                            value="{{ $tenantOption['id'] }}"
                                            @selected((int) old('tenant_id', $selectedTenantId) === (int) $tenantOption['id'])
                                        >
                                            {{ $tenantOption['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                @php
                                    $tenantName = collect($tenantOptions)->firstWhere('id', $selectedTenantId)['name'] ?? ('#'.$selectedTenantId);
                                @endphp
                                <input
                                    type="text"
                                    class="form-control form-control-solid"
                                    value="{{ $tenantName }}"
                                    readonly
                                >
                            @endif
                        </div>
                    @endif

                    <div class="col-12">
                        <label class="form-label fw-semibold" for="question">{{ __('Question') }}</label>
                        <textarea
                            id="question"
                            name="question"
                            rows="4"
                            class="form-control form-control-solid"
                            required
                        >{{ old('question', $question?->question) }}</textarea>
                    </div>

                    <div class="col-12 col-lg-4">
                        <label class="form-label fw-semibold" for="sort_order">{{ __('Display order') }}</label>
                        <input
                            id="sort_order"
                            name="sort_order"
                            type="number"
                            min="0"
                            max="100000"
                            class="form-control form-control-solid"
                            value="{{ old('sort_order', $question?->sort_order ?? 0) }}"
                        >
                    </div>

                    <div class="col-12 col-lg-4 d-flex align-items-center">
                        <div class="form-check form-switch form-check-custom form-check-solid mt-8">
                            <input type="hidden" name="is_active" value="0">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="is_active"
                                name="is_active"
                                value="1"
                                @checked((bool) old('is_active', $question?->is_active ?? true))
                            >
                            <label class="form-check-label ms-3" for="is_active">{{ __('Active question') }}</label>
                        </div>
                    </div>
                </div>

                <div class="pt-8">
                    <button class="btn btn-primary" type="submit">
                        {{ $mode === 'create' ? __('Create question') : __('Save changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
