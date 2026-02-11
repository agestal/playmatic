@extends('layouts.metronic.app')

@section('title', __('Permissions List'))
@section('page_title', __('Permissions List'))

@push('styles')
    <link href="{{ asset('assets/metronic8/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
@endpush

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
            <div class="card-title">
                <form action="{{ route('access.permissions.index') }}" id="permissionsSearchForm" method="GET">
                    <input name="group" type="hidden" value="{{ $groupFilter }}"/>
                    <input name="per_page" type="hidden" value="{{ $perPage }}"/>

                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <input
                            class="form-control form-control-solid w-250px ps-13"
                            data-kt-permission-table-filter="search"
                            name="search"
                            placeholder="{{ __('Search permission') }}"
                            type="text"
                            value="{{ $search }}"
                        />
                    </div>
                </form>
            </div>

            <div class="card-toolbar">
                <div class="d-flex justify-content-end" data-kt-permission-table-toolbar="base">
                    <button
                        class="btn btn-light-primary me-3"
                        data-kt-menu-placement="bottom-end"
                        data-kt-menu-trigger="click"
                        type="button"
                    >
                        <i class="ki-duotone ki-filter fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ __('Filter') }}
                    </button>

                    <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                        <form action="{{ route('access.permissions.index') }}" class="px-7 py-5" id="permissionsFilterForm" method="GET">
                            <div class="fs-5 text-gray-900 fw-bold mb-5">{{ __('Filter Options') }}</div>

                            <div class="mb-10">
                                <label class="form-label fs-6 fw-semibold">{{ __('Group') }}:</label>
                                <select class="form-select form-select-solid fw-bold" name="group">
                                    <option value="">{{ __('All groups') }}</option>
                                    @foreach ($groupOptions as $groupName)
                                        <option @selected($groupFilter === $groupName) value="{{ $groupName }}">{{ $groupName }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-10">
                                <label class="form-label fs-6 fw-semibold">{{ __('Rows per page') }}:</label>
                                <select class="form-select form-select-solid fw-bold" name="per_page">
                                    @foreach ([10, 25, 50, 100] as $option)
                                        <option @selected((int) $perPage === $option) value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <input name="search" type="hidden" value="{{ $search }}"/>

                            <div class="d-flex justify-content-end">
                                <a class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6" href="{{ route('access.permissions.index') }}">{{ __('Reset') }}</a>
                                <button class="btn btn-primary fw-semibold px-6" type="submit">{{ __('Apply') }}</button>
                            </div>
                        </form>
                    </div>

                    <a class="btn btn-light-primary" href="{{ route('access.roles.index') }}">
                        <i class="ki-duotone ki-shield-tick fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ __('Manage Roles') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_permissions">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-150px">{{ __('Permission') }}</th>
                        <th class="min-w-125px">{{ __('Group') }}</th>
                        <th class="min-w-150px">{{ __('Roles with access') }}</th>
                        <th class="min-w-250px">{{ __('Description') }}</th>
                        <th class="text-end min-w-100px">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    @forelse ($permissions as $permission)
                        @php
                            $roleNames = collect($permission['role_names']);
                            $rolePreview = $roleNames->take(3);
                            $hiddenRoles = max(0, (int) $permission['roles_count'] - $rolePreview->count());
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-800 mb-1">{{ $permission['label'] }}</span>
                                    <span class="text-muted fs-7">{{ $permission['name'] }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-light-primary fw-bold">{{ $permission['group'] }}</span>
                            </td>
                            <td>
                                <div class="mb-2">
                                    <span class="badge badge-light fw-bold">{{ $permission['roles_count'] }}</span>
                                </div>
                                @if ($rolePreview->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($rolePreview as $roleName)
                                            <span class="badge badge-light-success fw-semibold">{{ $roleName }}</span>
                                        @endforeach
                                        @if ($hiddenRoles > 0)
                                            <span class="badge badge-light-info fw-semibold">+{{ $hiddenRoles }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">{{ __('No roles assigned') }}</span>
                                @endif
                            </td>
                            <td>{{ $permission['description'] }}</td>
                            <td class="text-end">
                                <a
                                    class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm"
                                    data-kt-menu-placement="bottom-end"
                                    data-kt-menu-trigger="click"
                                    href="#"
                                >
                                    {{ __('Actions') }}
                                    <i class="ki-duotone ki-down fs-5 ms-1"></i>
                                </a>

                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                    <div class="menu-item px-3">
                                        <a class="menu-link px-3" href="{{ route('access.roles.index') }}">{{ __('View roles') }}</a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a class="menu-link px-3" href="{{ route('access.roles.create') }}">{{ __('Create role') }}</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-muted py-10" colspan="5">{{ __('No permissions found for this company.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($permissions->total() > 0)
                <div class="d-flex flex-stack flex-wrap pt-5">
                    <div class="fs-6 fw-semibold text-gray-700">
                        {{ __('Showing :from to :to of :total records', ['from' => $permissions->firstItem(), 'to' => $permissions->lastItem(), 'total' => $permissions->total()]) }}
                    </div>

                    <ul class="pagination">
                        <li class="page-item previous {{ $permissions->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $permissions->previousPageUrl() ?: '#' }}"><i class="previous"></i></a>
                        </li>

                        @foreach ($permissions->getUrlRange(1, $permissions->lastPage()) as $page => $url)
                            <li class="page-item {{ $page === $permissions->currentPage() ? 'active' : '' }}">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endforeach

                        <li class="page-item next {{ $permissions->hasMorePages() ? '' : 'disabled' }}">
                            <a class="page-link" href="{{ $permissions->nextPageUrl() ?: '#' }}"><i class="next"></i></a>
                        </li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/metronic8/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script>
        (function () {
            const searchInput = document.querySelector('[data-kt-permission-table-filter="search"]');
            const searchForm = document.getElementById('permissionsSearchForm');

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
