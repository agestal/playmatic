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

    @if ($mode === 'edit')
        <div class="card mt-8">
            <div class="card-header border-0 pt-6">
                <div class="card-title flex-column align-items-start">
                    <h3 class="fw-bold mb-1">{{ __('Answers') }}</h3>
                    <span class="text-muted fw-semibold fs-7">{{ __('Each question can only have one answer marked as correct.') }}</span>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-7 gy-3">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-300px">{{ __('Answer') }}</th>
                                <th class="min-w-120px">{{ __('Correct') }}</th>
                                <th class="text-end min-w-150px">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 fw-semibold">
                            @forelse ($question->answers as $answerItem)
                                <tr>
                                    <td>
                                        <form id="update_answer_{{ $answerItem->id }}" method="POST" action="{{ route('games.quiz.answers.update', ['answer' => $answerItem->id]) }}">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="question_id" value="{{ $question->id }}">
                                        </form>
                                        <input
                                            form="update_answer_{{ $answerItem->id }}"
                                            type="text"
                                            name="answer"
                                            class="form-control form-control-solid form-control-sm"
                                            value="{{ $answerItem->answer }}"
                                            required
                                        >
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-start">
                                            <div class="form-check form-switch form-check-custom form-check-solid">
                                                <input form="update_answer_{{ $answerItem->id }}" type="hidden" name="is_correct" value="0">
                                                <input
                                                    form="update_answer_{{ $answerItem->id }}"
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    name="is_correct"
                                                    value="1"
                                                    id="is_correct_{{ $answerItem->id }}"
                                                    @checked((bool) $answerItem->is_correct)
                                                >
                                                <label class="form-check-label ms-3" for="is_correct_{{ $answerItem->id }}">{{ __('Correct') }}</label>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex align-items-center gap-2">
                                            <button type="submit" form="update_answer_{{ $answerItem->id }}" class="btn btn-sm btn-light-primary">{{ __('Save changes') }}</button>
                                            <form method="POST" action="{{ route('games.quiz.answers.destroy', ['answer' => $answerItem->id]) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-light-danger"
                                                    onclick="return confirm('{{ __('Are you sure you want to delete this answer?') }}')"
                                                >
                                                    {{ __('Delete') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-6">{{ __('No quiz answers found.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @can('games.edit.content')
                    <div class="separator my-8"></div>
                    <h4 class="fw-bold mb-4">{{ __('Add answer') }}</h4>
                    <form method="POST" action="{{ route('games.quiz.answers.store') }}">
                        @csrf
                        @if ($isSuperadmin)
                            <input type="hidden" name="tenant_id" value="{{ $selectedTenantId }}">
                        @endif
                        <input type="hidden" name="question_id" value="{{ $question->id }}">

                        <div class="row g-6">
                            <div class="col-12">
                                <label class="form-label fw-semibold" for="new_answer">{{ __('Answer') }}</label>
                                <textarea id="new_answer" name="answer" rows="3" class="form-control form-control-solid" required>{{ old('answer') }}</textarea>
                            </div>
                            <div class="col-12 d-flex align-items-center">
                                <div class="form-check form-switch form-check-custom form-check-solid">
                                    <input type="hidden" name="is_correct" value="0">
                                    <input class="form-check-input" type="checkbox" id="new_is_correct" name="is_correct" value="1" @checked((bool) old('is_correct'))>
                                    <label class="form-check-label ms-3" for="new_is_correct">{{ __('Mark as correct answer') }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6">
                            <button class="btn btn-primary" type="submit">{{ __('Create answer') }}</button>
                        </div>
                    </form>
                @endcan
            </div>
        </div>
    @endif
@endsection
