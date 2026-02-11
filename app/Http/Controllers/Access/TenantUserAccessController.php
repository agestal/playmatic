<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenantUserAccessController extends Controller
{
    public function index(Request $request, TenantContext $tenantContext): View
    {
        $tenant = $this->tenantOrFail($tenantContext);

        $search = trim(strval($request->query('search', '')));
        $roleFilter = trim(strval($request->query('role', '')));
        $twoStepFilter = trim(strval($request->query('two_step', '')));
        $sort = trim(strval($request->query('sort', 'name')));
        $direction = strval($request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $perPage = intval($request->query('per_page', 10));

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $users = $this->usersQuery(
            tenantId: $tenant->id,
            search: $search,
            roleFilter: $roleFilter,
            twoStepFilter: $twoStepFilter,
            sort: $sort,
            direction: $direction,
        )->paginate($perPage)->withQueryString();

        $roleOptions = Role::query()
            ->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->pluck('name')
            ->all();

        $roleChoices = Role::query()
            ->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('users.index', [
            'users' => $users,
            'roleOptions' => $roleOptions,
            'roleChoices' => $roleChoices,
            'search' => $search,
            'roleFilter' => $roleFilter,
            'twoStepFilter' => $twoStepFilter,
            'sort' => $sort,
            'direction' => $direction,
            'perPage' => $perPage,
        ]);
    }

    public function store(Request $request, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $validated = $this->validateMembershipPayload($request, $tenant);

        $user = User::query()
            ->where('email', $validated['email'])
            ->firstOrFail();

        TenantUser::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
            ],
            [
                'role_id' => (int) $validated['role_id'],
                'status' => $validated['status'],
            ]
        );

        return redirect()
            ->route('users.index')
            ->with('status', __('User access was updated for this company.'));
    }

    public function update(Request $request, string $locale, TenantUser $tenantUser, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $this->assertMembershipTenant($tenantUser, $tenant);

        $validated = $request->validate([
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')->where(fn ($query) => $query->where('tenant_id', $tenant->id)),
            ],
            'status' => ['required', Rule::in(['active', 'disabled'])],
        ]);

        if ((int) $request->user()->id === (int) $tenantUser->user_id && $validated['status'] === 'disabled') {
            return redirect()
                ->route('users.index')
                ->withErrors(['membership' => __('You cannot disable your own access in this company.')]);
        }

        $tenantUser->update([
            'role_id' => (int) $validated['role_id'],
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('users.index')
            ->with('status', __('User permissions updated.'));
    }

    public function destroy(Request $request, string $locale, TenantUser $tenantUser, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $this->assertMembershipTenant($tenantUser, $tenant);

        if ((int) $request->user()->id === (int) $tenantUser->user_id) {
            return redirect()
                ->route('users.index')
                ->withErrors(['membership' => __('You cannot remove your own access from here.')]);
        }

        $tenantUser->delete();

        return redirect()
            ->route('users.index')
            ->with('status', __('Access removed successfully.'));
    }

    public function destroyMany(Request $request, string $locale, TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $this->tenantOrFail($tenantContext);
        $validated = $request->validate([
            'tenant_user_ids' => ['nullable', 'array'],
            'tenant_user_ids.*' => ['integer'],
        ]);

        $ids = collect(data_get($validated, 'tenant_user_ids', []))
            ->map(fn ($id): int => intval($id))
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return redirect()->route('users.index');
        }

        $memberships = TenantUser::query()
            ->where('tenant_id', $tenant->id)
            ->whereIn('id', $ids->all())
            ->get(['id', 'user_id']);

        $currentUserId = intval($request->user()?->id);

        $deletableIds = $memberships
            ->filter(fn (TenantUser $membership): bool => intval($membership->user_id) !== $currentUserId)
            ->pluck('id')
            ->all();

        if (! empty($deletableIds)) {
            TenantUser::query()
                ->where('tenant_id', $tenant->id)
                ->whereIn('id', $deletableIds)
                ->delete();
        }

        if (count($deletableIds) !== $memberships->count()) {
            return redirect()
                ->route('users.index')
                ->withErrors(['membership' => __('You cannot remove your own access from here.')]);
        }

        return redirect()
            ->route('users.index')
            ->with('status', __('Access removed successfully.'));
    }

    /**
     * @return array{email:string,role_id:int|string,status:string}
     */
    protected function validateMembershipPayload(Request $request, Tenant $tenant): array
    {
        return $request->validate([
            'email' => ['required', 'email', Rule::exists('users', 'email')],
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')->where(fn ($query) => $query->where('tenant_id', $tenant->id)),
            ],
            'status' => ['required', Rule::in(['active', 'disabled'])],
        ]);
    }

    protected function tenantOrFail(TenantContext $tenantContext): Tenant
    {
        $tenant = $tenantContext->tenant();

        if (! $tenant) {
            abort(404, __('There is no active company for this domain.'));
        }

        return $tenant;
    }

    protected function assertMembershipTenant(TenantUser $tenantUser, Tenant $tenant): void
    {
        if ((int) $tenantUser->tenant_id !== (int) $tenant->id) {
            abort(404);
        }
    }

    protected function usersQuery(
        int $tenantId,
        string $search,
        string $roleFilter,
        string $twoStepFilter,
        string $sort,
        string $direction,
    ): Builder {
        $query = User::query()->select('users.*');

        $query
            ->join('tenant_users', function ($join) use ($tenantId): void {
                $join->on('tenant_users.user_id', '=', 'users.id')
                    ->where('tenant_users.tenant_id', '=', $tenantId);
            })
            ->leftJoin('roles', 'roles.id', '=', 'tenant_users.role_id')
            ->selectSub(
                DB::table('sessions')
                    ->selectRaw('MAX(last_activity)')
                    ->whereColumn('sessions.user_id', 'users.id'),
                'last_seen_activity'
            )
            ->addSelect([
                DB::raw('tenant_users.id as membership_id'),
                DB::raw('tenant_users.status as membership_status'),
                DB::raw('roles.name as membership_role_name'),
            ]);

        if ($search !== '') {
            $like = '%'.$search.'%';

            $query->where(function (Builder $searchQuery) use ($like): void {
                $searchQuery
                    ->where('users.name', 'like', $like)
                    ->orWhere('users.email', 'like', $like)
                    ->orWhere('roles.name', 'like', $like);
            });
        }

        if ($roleFilter !== '') {
            $query->where('roles.name', $roleFilter);
        }

        if ($twoStepFilter === 'enabled') {
            $query->whereNotNull('users.email_verified_at');
        }

        if ($twoStepFilter === 'disabled') {
            $query->whereNull('users.email_verified_at');
        }

        $sortableColumns = [
            'name' => 'users.name',
            'role' => 'roles.name',
            'last_login' => 'last_seen_activity',
            'two_step' => 'users.email_verified_at',
            'joined' => 'users.created_at',
        ];

        $sortColumn = data_get($sortableColumns, $sort, 'users.name');

        if ($sort === 'two_step') {
            $query->orderByRaw('CASE WHEN users.email_verified_at IS NULL THEN 0 ELSE 1 END '.$direction)
                ->orderBy('users.name');
        } else {
            $query->orderBy($sortColumn, $direction)->orderBy('users.name');
        }

        return $query;
    }
}
