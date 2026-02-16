@extends('layouts.metronic.app')

@section('title', __('Quiz Questions'))
@section('page_title', __('Quiz Questions'))

@section('content')
    @if (session('status'))
        <div class="alert alert-success mb-6">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-6">{{ $errors->first() }}</div>
    @endif

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <form id="quizQuestionSearchForm" method="GET" action="{{ route('games.quiz.questions.index') }}">
                    <input type="hidden" name="is_active" value="{{ $activeFilter }}">
                    <input type="hidden" name="tenant_id" value="{{ $isSuperadmin ? ($tenantFilter > 0 ? $tenantFilter : '') : '' }}">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">

                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <input
                            type="text"
                            name="search"
                            class="form-control form-control-solid w-300px ps-13"
                            placeholder="{{ __('Search question') }}"
                            value="{{ $search }}"
                            data-quiz-question-filter="search"
                        >
                    </div>
                </form>
            </div>

            <div class="card-toolbar d-flex gap-3">
                <button
                    type="button"
                    class="btn btn-light-primary"
                    data-kt-menu-trigger="click"
                    data-kt-menu-placement="bottom-end"
                >
                    <i class="ki-duotone ki-filter fs-2"></i>
                    {{ __('Filter') }}
                </button>

                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                    <form class="px-7 py-5" method="GET" action="{{ route('games.quiz.questions.index') }}">
                        <div class="fs-5 text-gray-900 fw-bold mb-5">{{ __('Filter options') }}</div>

                        @if ($isSuperadmin)
                            <div class="mb-8">
                                <label class="form-label fw-semibold">{{ __('Tenant') }}</label>
                                <select name="tenant_id" class="form-select form-select-solid">
                                    <option value="">{{ __('All tenants') }}</option>
                                    @foreach ($tenantOptions as $tenantOption)
                                        <option value="{{ $tenantOption['id'] }}" @selected((int) $tenantFilter === (int) $tenantOption['id'])>
                                            {{ $tenantOption['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="mb-8">
                            <label class="form-label fw-semibold">{{ __('Status') }}</label>
                            <select name="is_active" class="form-select form-select-solid">
                                <option value="">{{ __('Any status') }}</option>
                                <option value="1" @selected($activeFilter === '1')>{{ __('Active') }}</option>
                                <option value="0" @selected($activeFilter === '0')>{{ __('Inactive') }}</option>
                            </select>
                        </div>

                        <div class="mb-8">
                            <label class="form-label fw-semibold">{{ __('Rows per page') }}</label>
                            <select name="per_page" class="form-select form-select-solid">
                                @foreach ([10, 25, 50, 100] as $option)
                                    <option value="{{ $option }}" @selected((int) $perPage === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>

                        <input type="hidden" name="search" value="{{ $search }}">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('games.quiz.questions.index') }}" class="btn btn-light btn-sm">{{ __('Reset') }}</a>
                            <button type="submit" class="btn btn-primary btn-sm">{{ __('Apply') }}</button>
                        </div>
                    </form>
                </div>

                @can('games.edit.content')
                    <a class="btn btn-primary" href="{{ route('games.quiz.questions.create') }}">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        {{ __('Add question') }}
                    </a>
                @endcan
            </div>
        </div>

        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-60px">#</th>
                        @if ($isSuperadmin)
                            <th class="min-w-150px">{{ __('Tenant') }}</th>
                        @endif
                        <th class="min-w-260px">{{ __('Question') }}</th>
                        <th class="min-w-100px">{{ __('Answers') }}</th>
                        <th class="min-w-100px">{{ __('Correct') }}</th>
                        <th class="min-w-90px">{{ __('Status') }}</th>
                        <th class="min-w-90px">{{ __('Order') }}</th>
                        <th class="text-end min-w-100px">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 fw-semibold">
                    @forelse ($questions as $row)
                        <tr>
                            <td>#{{ $row->id }}</td>
                            @if ($isSuperadmin)
                                <td>{{ $row->tenant?->name ?? '-' }}</td>
                            @endif
                            <td>{{ str($row->question)->limit(180)->toString() }}</td>
                            <td>{{ $row->answers_count }}</td>
                            <td>
                                @if ($row->correctAnswer)
                                    <span class="badge badge-light-success">{{ __('Defined') }}</span>
                                @else
                                    <span class="badge badge-light-warning">{{ __('Missing') }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($row->is_active)
                                    <span class="badge badge-light-success">{{ __('Active') }}</span>
                                @else
                                    <span class="badge badge-light">{{ __('Inactive') }}</span>
                                @endif
                            </td>
                            <td>{{ $row->sort_order }}</td>
                            <td class="text-end">
                                @can('games.edit.content')
                                    <a class="btn btn-sm btn-light-primary" href="{{ route('games.quiz.questions.edit', ['question' => $row->id]) }}">{{ __('Edit') }}</a>

                                    <form class="d-inline" method="POST" action="{{ route('games.quiz.questions.destroy', ['question' => $row->id]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-light-danger"
                                            onclick="return confirm('{{ __('Are you sure you want to delete this question?') }}')"
                                        >
                                            {{ __('Delete') }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">-</span>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isSuperadmin ? 8 : 7 }}" class="text-center text-muted py-10">{{ __('No quiz questions found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($questions->total() > 0)
                <div class="d-flex flex-stack flex-wrap pt-5">
                    <div class="fs-6 fw-semibold text-gray-700">
                        {{ __('Showing :from to :to of :total records', ['from' => $questions->firstItem(), 'to' => $questions->lastItem(), 'total' => $questions->total()]) }}
                    </div>

                    <ul class="pagination">
                        <li class="page-item previous {{ $questions->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $questions->previousPageUrl() ?: '#' }}"><i class="previous"></i></a>
                        </li>

                        @foreach ($questions->getUrlRange(1, $questions->lastPage()) as $page => $url)
                            <li class="page-item {{ $page === $questions->currentPage() ? 'active' : '' }}">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endforeach

                        <li class="page-item next {{ $questions->hasMorePages() ? '' : 'disabled' }}">
                            <a class="page-link" href="{{ $questions->nextPageUrl() ?: '#' }}"><i class="next"></i></a>
                        </li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const searchInput = document.querySelector('[data-quiz-question-filter="search"]');
            const searchForm = document.getElementById('quizQuestionSearchForm');

            if (!searchInput || !searchForm) {
                return;
            }

            let timer = null;

            searchInput.addEventListener('keyup', function () {
                if (timer) {
                    clearTimeout(timer);
                }

                timer = setTimeout(function () {
                    searchForm.submit();
                }, 450);
            });
        })();
    </script>
@endpush
