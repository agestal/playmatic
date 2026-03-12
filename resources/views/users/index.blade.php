@extends('layouts.metronic.app')

@section('title', __('Users List'))
@section('page_title', __('Users List'))

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
                <form action="{{ route('users.index') }}" id="usersSearchForm" method="GET">
                    <input name="role" type="hidden" value="{{ $roleFilter }}"/>
                    <input name="two_step" type="hidden" value="{{ $twoStepFilter }}"/>
                    <input name="per_page" type="hidden" value="{{ $perPage }}"/>

                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <input
                            class="form-control form-control-solid w-250px ps-13"
                            data-kt-user-table-filter="search"
                            name="search"
                            placeholder="{{ __('Search user') }}"
                            type="text"
                            value="{{ $search }}"
                        />
                    </div>
                </form>
            </div>

            <div class="card-toolbar">
                <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
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
                        <form action="{{ route('users.index') }}" class="px-7 py-5" data-kt-user-table-filter="form" id="usersFilterForm" method="GET">
                            <div class="fs-5 text-gray-900 fw-bold mb-5">{{ __('Filter Options') }}</div>

                            <div class="mb-10">
                                <label class="form-label fs-6 fw-semibold">{{ __('Role') }}:</label>
                                <select class="form-select form-select-solid fw-bold" name="role">
                                    <option value="">{{ __('All roles') }}</option>
                                    @foreach ($roleOptions as $roleName)
                                        <option @selected($roleFilter === $roleName) value="{{ $roleName }}">{{ $roleName }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-10">
                                <label class="form-label fs-6 fw-semibold">{{ __('Two Step Verification') }}:</label>
                                <select class="form-select form-select-solid fw-bold" name="two_step">
                                    <option value="">{{ __('Any') }}</option>
                                    <option @selected($twoStepFilter === 'enabled') value="enabled">{{ __('Enabled') }}</option>
                                    <option @selected($twoStepFilter === 'disabled') value="disabled">{{ __('Disabled') }}</option>
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
                                <a class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6" href="{{ route('users.index') }}">{{ __('Reset') }}</a>
                                <button class="btn btn-primary fw-semibold px-6" data-kt-user-table-filter="filter" type="submit">{{ __('Apply') }}</button>
                            </div>
                        </form>
                    </div>

                    <button class="btn btn-light-primary me-3" data-bs-target="#kt_modal_export_users" data-bs-toggle="modal" type="button">
                        <i class="ki-duotone ki-exit-up fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ __('Export') }}
                    </button>

                    @if (auth()->user()?->can('tenant.roles.manage'))
                        <a class="btn btn-light-primary me-3" href="{{ route('access.permissions.index') }}">
                            <i class="ki-duotone ki-shield-search fs-2"></i>
                            {{ __('Roles & Permissions') }}
                        </a>
                    @endif

                    <button class="btn btn-primary" data-bs-target="#kt_modal_add_user" data-bs-toggle="modal" type="button">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        {{ __('Add User') }}
                    </button>
                </div>

                <div class="d-flex justify-content-end align-items-center d-none" data-kt-user-table-toolbar="selected">
                    <div class="fw-bold me-5">
                        <span class="me-2" data-kt-user-table-select="selected_count"></span>
                        {{ __('Selected') }}
                    </div>

                    <form action="{{ route('users.destroy-many') }}" id="bulkDeleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <div id="bulkDeleteInputs"></div>
                    </form>

                    <button class="btn btn-danger" data-kt-user-table-select="delete_selected" type="button">
                        {{ __('Delete Selected') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-10px pe-2">
                            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                <input class="form-check-input" data-kt-check="true" data-kt-check-target="#kt_table_users .row-checkbox" type="checkbox" value="1"/>
                            </div>
                        </th>
                        <th class="min-w-125px">{{ __('User') }}</th>
                        <th class="min-w-125px">{{ __('Role') }}</th>
                        <th class="min-w-125px">{{ __('Last login') }}</th>
                        <th class="min-w-125px">{{ __('Two-step') }}</th>
                        <th class="min-w-125px">{{ __('Joined Date') }}</th>
                        <th class="text-end min-w-100px">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    @forelse ($users as $member)
                        @php
                            $initials = collect(preg_split('/\s+/', trim((string) $member->name)) ?: [])
                                ->filter()
                                ->map(static fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
                                ->take(2)
                                ->implode('');

                            if ($initials === '') {
                                $initials = 'U';
                            }

                            $lastSeen = $member->last_seen_activity ? \Illuminate\Support\Carbon::createFromTimestamp((int) $member->last_seen_activity) : null;
                            $lastSeenLabel = $lastSeen ? $lastSeen->diffForHumans() : __('Never');
                            $joinedAt = $member->created_at ? $member->created_at->format('d M Y, g:i A') : '-';
                        @endphp
                        <tr data-membership-id="{{ $member->membership_id }}">
                            <td>
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input row-checkbox" type="checkbox" value="{{ $member->membership_id }}"/>
                                </div>
                            </td>
                            <td class="d-flex align-items-center">
                                <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                    <a href="{{ route('users.edit', ['tenantUser' => $member->membership_id]) }}">
                                        <div class="symbol-label fs-3 bg-light-primary text-primary fw-bold">{{ $initials }}</div>
                                    </a>
                                </div>

                                <div class="d-flex flex-column">
                                    <a class="text-gray-800 text-hover-primary mb-1" href="{{ route('users.edit', ['tenantUser' => $member->membership_id]) }}">{{ $member->name }}</a>
                                    <span>{{ $member->email }}</span>
                                </div>
                            </td>
                            <td>{{ $member->membership_role_name ?: __('No role') }}</td>
                            <td>
                                <div class="badge badge-light fw-bold">{{ $lastSeenLabel }}</div>
                            </td>
                            <td>
                                @if ($member->email_verified_at)
                                    <div class="badge badge-light-success fw-bold">{{ __('Enabled') }}</div>
                                @else
                                    <div class="badge badge-light-danger fw-bold">{{ __('Disabled') }}</div>
                                @endif
                            </td>
                            <td>{{ $joinedAt }}</td>
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
                                        <a class="menu-link px-3" href="{{ route('users.edit', ['tenantUser' => $member->membership_id]) }}">{{ __('Edit') }}</a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <button
                                            class="menu-link px-3 border-0 bg-transparent w-100 text-start"
                                            data-kt-users-table-filter="delete_row"
                                            data-membership-id="{{ $member->membership_id }}"
                                            data-user-name="{{ $member->name }}"
                                            type="button"
                                        >
                                            {{ __('Delete') }}
                                        </button>
                                    </div>
                                </div>

                                <form action="{{ route('users.destroy', ['tenantUser' => $member->membership_id]) }}" id="delete-membership-{{ $member->membership_id }}" method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-muted py-10" colspan="7">{{ __('No users found for this company.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($users->total() > 0)
                <div class="d-flex flex-stack flex-wrap pt-5">
                    <div class="fs-6 fw-semibold text-gray-700">
                        {{ __('Showing :from to :to of :total records', ['from' => $users->firstItem(), 'to' => $users->lastItem(), 'total' => $users->total()]) }}
                    </div>

                    <ul class="pagination">
                        <li class="page-item previous {{ $users->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $users->previousPageUrl() ?: '#' }}"><i class="previous"></i></a>
                        </li>

                        @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                            <li class="page-item {{ $page === $users->currentPage() ? 'active' : '' }}">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endforeach

                        <li class="page-item next {{ $users->hasMorePages() ? '' : 'disabled' }}">
                            <a class="page-link" href="{{ $users->nextPageUrl() ?: '#' }}"><i class="next"></i></a>
                        </li>
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="kt_modal_export_users" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">{{ __('Export Users') }}</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"></i>
                    </div>
                </div>

                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <form class="form" id="kt_modal_export_users_form" action="#">
                        <div class="fv-row mb-10">
                            <label class="fs-6 fw-semibold form-label mb-2">{{ __('Select export format:') }}</label>
                            <select class="form-select form-select-solid fw-bold" name="format">
                                <option value="csv">CSV</option>
                                <option value="xlsx">Excel</option>
                            </select>
                        </div>

                        <div class="text-center">
                            <button class="btn btn-light me-3" data-bs-dismiss="modal" type="button">{{ __('Cancel') }}</button>
                            <button class="btn btn-primary" data-kt-users-modal-action="submit" type="button">{{ __('Export') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="kt_modal_add_user" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header" id="kt_modal_add_user_header">
                    <h2 class="fw-bold">{{ __('Add User') }}</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"></i>
                    </div>
                </div>

                <div class="modal-body px-5 my-7">
                    <form action="{{ route('users.store') }}" class="form" id="kt_modal_add_user_form" method="POST">
                        @csrf

                        <div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_add_user_scroll" style="max-height: 70vh;">
                            <div class="fv-row mb-7">
                                <label class="required fw-semibold fs-6 mb-2">{{ __('User email') }}</label>
                                <input class="form-control form-control-solid mb-3 mb-lg-0" name="email" placeholder="name@example.com" required type="email" value="{{ old('email') }}"/>
                                <div class="form-text">{{ __('If the user does not exist, it will be created automatically.') }}</div>
                            </div>

                            <div class="fv-row mb-7">
                                <label class="fw-semibold fs-6 mb-2">{{ __('Password') }}</label>
                                <input class="form-control form-control-solid mb-3 mb-lg-0" autocomplete="new-password" name="password" type="password"/>
                                <div class="form-text">{{ __('Set a password for new users. For existing users, leave empty to keep the current password.') }}</div>
                            </div>

                            <div class="fv-row mb-7">
                                <label class="fw-semibold fs-6 mb-2">{{ __('Confirm Password') }}</label>
                                <input class="form-control form-control-solid mb-3 mb-lg-0" autocomplete="new-password" name="password_confirmation" type="password"/>
                            </div>

                            <div class="fv-row mb-7">
                                <label class="required fw-semibold fs-6 mb-2">{{ __('Role') }}</label>
                                <select class="form-select form-select-solid" name="role_id" required>
                                    <option value="">{{ __('Select role') }}</option>
                                    @foreach ($roleChoices as $roleChoice)
                                        <option @selected((string) old('role_id') === (string) $roleChoice->id) value="{{ $roleChoice->id }}">{{ $roleChoice->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="fv-row mb-7">
                                <label class="required fw-semibold fs-6 mb-2">{{ __('Status') }}</label>
                                <select class="form-select form-select-solid" name="status" required>
                                    <option @selected(old('status', 'active') === 'active') value="active">{{ __('Active') }}</option>
                                    <option @selected(old('status') === 'disabled') value="disabled">{{ __('Disabled') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="text-center pt-10">
                            <button class="btn btn-light me-3" data-bs-dismiss="modal" type="button">{{ __('Discard') }}</button>
                            <button class="btn btn-primary" type="submit">{{ __('Submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/metronic8/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script>
        window.playmaticUsersTableI18n = {
            deleteConfirmTemplate: @json(__('Are you sure you want to delete :name?', ['name' => '__name__'])),
            deleteSelectedConfirm: @json(__('Are you sure you want to delete selected users?')),
            confirmDelete: @json(__('Yes, delete!')),
            cancelDelete: @json(__('No, cancel')),
            defaultUserName: @json(__('User')),
            csvUser: @json(__('User')),
            csvRole: @json(__('Role')),
            csvLastLogin: @json(__('Last login')),
            csvTwoStep: @json(__('Two-step')),
            csvJoinedDate: @json(__('Joined Date')),
            csvFileName: 'users-export.csv',
        };
    </script>
    <script src="{{ asset('assets/metronic8/js/custom/apps/user-management/users/list/playmatic-table.js') }}"></script>
@endpush
