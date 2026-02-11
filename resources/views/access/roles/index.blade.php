@extends('layouts.metronic.app')

@section('title', __('Roles List'))
@section('page_title', __('Roles List'))

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
                <form action="{{ route('access.roles.index') }}" id="rolesSearchForm" method="GET">
                    <input name="permission" type="hidden" value="{{ $permissionFilter }}"/>
                    <input name="per_page" type="hidden" value="{{ $perPage }}"/>

                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <input
                            class="form-control form-control-solid w-250px ps-13"
                            data-kt-role-table-filter="search"
                            name="search"
                            placeholder="{{ __('Search role') }}"
                            type="text"
                            value="{{ $search }}"
                        />
                    </div>
                </form>
            </div>

            <div class="card-toolbar">
                <div class="d-flex justify-content-end" data-kt-role-table-toolbar="base">
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
                        <form action="{{ route('access.roles.index') }}" class="px-7 py-5" id="rolesFilterForm" method="GET">
                            <div class="fs-5 text-gray-900 fw-bold mb-5">{{ __('Filter Options') }}</div>

                            <div class="mb-10">
                                <label class="form-label fs-6 fw-semibold">{{ __('Permission') }}:</label>
                                <select class="form-select form-select-solid fw-bold" name="permission">
                                    <option value="">{{ __('Any permission') }}</option>
                                    @foreach ($permissionOptions as $permissionName)
                                        <option @selected($permissionFilter === $permissionName) value="{{ $permissionName }}">{{ $permissionName }}</option>
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
                                <a class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6" href="{{ route('access.roles.index') }}">{{ __('Reset') }}</a>
                                <button class="btn btn-primary fw-semibold px-6" type="submit">{{ __('Apply') }}</button>
                            </div>
                        </form>
                    </div>

                    <a class="btn btn-primary" href="{{ route('access.roles.create') }}">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        {{ __('Add Role') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_roles">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-150px">{{ __('Role') }}</th>
                        <th class="min-w-150px">{{ __('Permissions') }}</th>
                        <th class="min-w-100px">{{ __('Members') }}</th>
                        <th class="min-w-125px">{{ __('Created Date') }}</th>
                        <th class="text-end min-w-100px">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    @forelse ($roles as $role)
                        @php
                            $initials = collect(preg_split('/[\s\-_]+/', trim((string) $role->name)) ?: [])
                                ->filter()
                                ->map(static fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
                                ->take(2)
                                ->implode('');

                            if ($initials === '') {
                                $initials = 'R';
                            }

                            $permissionsPreview = $role->permissions->pluck('name')->take(3);
                            $hiddenPermissionsCount = max(0, (int) $role->permissions_count - $permissionsPreview->count());
                            $createdAt = $role->created_at ? $role->created_at->format('d M Y, g:i A') : '-';
                        @endphp
                        <tr>
                            <td class="d-flex align-items-center">
                                <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                    <a href="{{ route('access.roles.edit', ['role' => $role]) }}">
                                        <div class="symbol-label fs-3 bg-light-primary text-primary fw-bold">{{ $initials }}</div>
                                    </a>
                                </div>

                                <div class="d-flex flex-column">
                                    <a class="text-gray-800 text-hover-primary mb-1" href="{{ route('access.roles.edit', ['role' => $role]) }}">{{ $role->name }}</a>
                                    <span>{{ __('Guard') }}: {{ $role->guard_name }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="mb-2">
                                    <span class="badge badge-light-primary fw-bold">{{ __(':count assigned', ['count' => $role->permissions_count]) }}</span>
                                </div>

                                @if ($permissionsPreview->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($permissionsPreview as $permissionName)
                                            <span class="badge badge-light fw-semibold">{{ $permissionName }}</span>
                                        @endforeach
                                        @if ($hiddenPermissionsCount > 0)
                                            <span class="badge badge-light-info fw-semibold">+{{ $hiddenPermissionsCount }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">{{ __('No permissions') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-light fw-bold">{{ $role->members_count }}</span>
                            </td>
                            <td>{{ $createdAt }}</td>
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
                                        <a class="menu-link px-3" href="{{ route('access.roles.edit', ['role' => $role]) }}">{{ __('Edit') }}</a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <button
                                            class="menu-link px-3 border-0 bg-transparent w-100 text-start"
                                            data-kt-roles-table-filter="delete_row"
                                            data-role-id="{{ $role->id }}"
                                            data-role-name="{{ $role->name }}"
                                            type="button"
                                        >
                                            {{ __('Delete') }}
                                        </button>
                                    </div>
                                </div>

                                <form action="{{ route('access.roles.destroy', ['role' => $role]) }}" class="d-none" id="delete-role-{{ $role->id }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-muted py-10" colspan="5">{{ __('No roles found for this company.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($roles->total() > 0)
                <div class="d-flex flex-stack flex-wrap pt-5">
                    <div class="fs-6 fw-semibold text-gray-700">
                        {{ __('Showing :from to :to of :total records', ['from' => $roles->firstItem(), 'to' => $roles->lastItem(), 'total' => $roles->total()]) }}
                    </div>

                    <ul class="pagination">
                        <li class="page-item previous {{ $roles->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $roles->previousPageUrl() ?: '#' }}"><i class="previous"></i></a>
                        </li>

                        @foreach ($roles->getUrlRange(1, $roles->lastPage()) as $page => $url)
                            <li class="page-item {{ $page === $roles->currentPage() ? 'active' : '' }}">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endforeach

                        <li class="page-item next {{ $roles->hasMorePages() ? '' : 'disabled' }}">
                            <a class="page-link" href="{{ $roles->nextPageUrl() ?: '#' }}"><i class="next"></i></a>
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
            const searchInput = document.querySelector('[data-kt-role-table-filter="search"]');
            const searchForm = document.getElementById('rolesSearchForm');

            if (searchInput && searchForm) {
                let timer = null;

                searchInput.addEventListener('keyup', function () {
                    if (timer) {
                        clearTimeout(timer);
                    }

                    timer = setTimeout(function () {
                        searchForm.submit();
                    }, 450);
                });
            }

            document.querySelectorAll('[data-kt-roles-table-filter="delete_row"]').forEach(function (button) {
                if (button.dataset.bound === 'true') {
                    return;
                }

                button.dataset.bound = 'true';

                button.addEventListener('click', function (event) {
                    event.preventDefault();

                    const roleId = button.getAttribute('data-role-id');
                    const roleName = button.getAttribute('data-role-name') || 'role';
                    const form = document.getElementById('delete-role-' + roleId);

                    if (!form) {
                        return;
                    }

                    if (window.Swal) {
                        Swal.fire({
                            text: 'Are you sure you want to delete ' + roleName + '?',
                            icon: 'warning',
                            showCancelButton: true,
                            buttonsStyling: false,
                            confirmButtonText: 'Yes, delete!',
                            cancelButtonText: 'No, cancel',
                            customClass: {
                                confirmButton: 'btn fw-bold btn-danger',
                                cancelButton: 'btn fw-bold btn-active-light-primary'
                            }
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });

                        return;
                    }

                    if (window.confirm('Are you sure you want to delete ' + roleName + '?')) {
                        form.submit();
                    }
                });
            });
        })();
    </script>
@endpush
