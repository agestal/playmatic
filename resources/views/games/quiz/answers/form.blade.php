@extends('layouts.metronic.app')

@section('title', $mode === 'create' ? __('Create quiz answer') : __('Edit quiz answer'))
@section('page_title', $mode === 'create' ? __('Create quiz answer') : __('Edit quiz answer'))

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title flex-column align-items-start">
                <h3 class="fw-bold mb-1">
                    {{ $mode === 'create' ? __('New quiz answer') : __('Edit quiz answer #:id', ['id' => $answer->id]) }}
                </h3>
                <span class="text-muted fw-semibold fs-7">{{ __('Each question can only have one answer marked as correct.') }}</span>
            </div>

            <div class="card-toolbar">
                <a class="btn btn-light-primary btn-sm" href="{{ route('games.quiz.answers.index') }}">
                    <i class="ki-duotone ki-left fs-3"></i>
                    {{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="card-body pt-0">
            <form
                method="POST"
                action="{{ $mode === 'create' ? route('games.quiz.answers.store') : route('games.quiz.answers.update', ['answer' => $answer->id]) }}"
                id="quizAnswerForm"
            >
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
                                <div class="form-text">{{ __('Changing the tenant reloads the available questions.') }}</div>
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
                        <label class="form-label fw-semibold" for="question_id">{{ __('Question') }}</label>
                        <select id="question_id" name="question_id" class="form-select form-select-solid" required>
                            <option value="">{{ __('Select question') }}</option>
                            @foreach ($questionOptions as $questionOption)
                                <option
                                    value="{{ $questionOption['id'] }}"
                                    @selected((int) old('question_id', $answer?->question_id) === (int) $questionOption['id'])
                                >
                                    {{ $questionOption['label'] }}
                                </option>
                            @endforeach
                        </select>
                        @if (count($questionOptions) === 0)
                            <div class="text-warning fs-7 mt-2">{{ __('No questions available for the selected tenant.') }}</div>
                        @endif
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold" for="answer">{{ __('Answer') }}</label>
                        <textarea
                            id="answer"
                            name="answer"
                            rows="4"
                            class="form-control form-control-solid"
                            required
                        >{{ old('answer', $answer?->answer) }}</textarea>
                    </div>

                    <div class="col-12 d-flex align-items-center">
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input type="hidden" name="is_correct" value="0">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="is_correct"
                                name="is_correct"
                                value="1"
                                @checked((bool) old('is_correct', $answer?->is_correct ?? false))
                            >
                            <label class="form-check-label ms-3" for="is_correct">{{ __('Mark as correct answer') }}</label>
                        </div>
                    </div>
                </div>

                <div class="pt-8">
                    <button class="btn btn-primary" type="submit" @disabled(count($questionOptions) === 0)>
                        {{ $mode === 'create' ? __('Create answer') : __('Save changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const tenantSelect = document.getElementById('tenant_id');
            const form = document.getElementById('quizAnswerForm');

            if (!tenantSelect || !form) {
                return;
            }

            tenantSelect.addEventListener('change', function () {
                const tenantId = tenantSelect.value;
                const targetUrl = new URL('{{ route('games.quiz.answers.create') }}', window.location.origin);

                if (tenantId) {
                    targetUrl.searchParams.set('tenant_id', tenantId);
                }

                window.location.href = targetUrl.toString();
            });
        })();
    </script>
@endpush
