<?php

namespace App\Http\Controllers;

use App\Models\TenantUser;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(TenantContext $tenantContext): View
    {
        $tenant = $tenantContext->tenant();
        $tenantId = $tenant?->id;

        $totalUsers = $this->totalUsers($tenantId);
        $activeUsers = $this->activeUsers($tenantId);
        $inactiveUsers = max($totalUsers - $activeUsers, 0);
        $verifiedUsers = $this->verifiedUsers($tenantId);
        $onlineLast24h = $this->onlineLast24h($tenantId);

        [$monthlyLabels, $monthlySeries] = $this->monthlySeries($tenantId);

        $currentMonthUsers = (int) ($monthlySeries[array_key_last($monthlySeries)] ?? 0);
        $previousMonthUsers = (int) ($monthlySeries[count($monthlySeries) - 2] ?? 0);
        $monthlyDelta = $currentMonthUsers - $previousMonthUsers;

        return view('app.dashboard', [
            'tenant' => $tenant,
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'inactiveUsers' => $inactiveUsers,
            'verifiedUsers' => $verifiedUsers,
            'onlineLast24h' => $onlineLast24h,
            'activityRate' => $this->percentage($activeUsers, $totalUsers),
            'verificationRate' => $this->percentage($verifiedUsers, $totalUsers),
            'monthlyLabels' => $monthlyLabels,
            'monthlySeries' => $monthlySeries,
            'monthlyDelta' => $monthlyDelta,
        ]);
    }

    protected function totalUsers(?int $tenantId): int
    {
        if ($tenantId) {
            return TenantUser::query()
                ->where('tenant_id', $tenantId)
                ->count();
        }

        return User::query()->count();
    }

    protected function activeUsers(?int $tenantId): int
    {
        $query = TenantUser::query()->where('status', 'active');

        if ($tenantId) {
            return $query
                ->where('tenant_id', $tenantId)
                ->count();
        }

        return $query
            ->distinct('user_id')
            ->count('user_id');
    }

    protected function verifiedUsers(?int $tenantId): int
    {
        if ($tenantId) {
            return TenantUser::query()
                ->join('users', 'users.id', '=', 'tenant_users.user_id')
                ->where('tenant_users.tenant_id', $tenantId)
                ->whereNotNull('users.email_verified_at')
                ->count();
        }

        return User::query()
            ->whereNotNull('email_verified_at')
            ->count();
    }

    protected function onlineLast24h(?int $tenantId): int
    {
        $query = DB::table('sessions')
            ->whereNotNull('sessions.user_id')
            ->where('sessions.last_activity', '>=', now()->subDay()->timestamp);

        if ($tenantId) {
            $query
                ->join('tenant_users', 'tenant_users.user_id', '=', 'sessions.user_id')
                ->where('tenant_users.tenant_id', $tenantId)
                ->where('tenant_users.status', 'active');
        }

        return (int) $query
            ->distinct('sessions.user_id')
            ->count('sessions.user_id');
    }

    /**
     * @return array{0:list<string>,1:list<int>}
     */
    protected function monthlySeries(?int $tenantId): array
    {
        $windowStart = now()->startOfMonth()->subMonths(5);

        $months = collect(range(0, 5))
            ->map(fn (int $offset): Carbon => $windowStart->copy()->addMonths($offset));

        $countsByMonth = $months
            ->mapWithKeys(fn (Carbon $month): array => [$month->format('Y-m') => 0])
            ->all();

        $rows = $tenantId
            ? TenantUser::query()
                ->where('tenant_id', $tenantId)
                ->where('created_at', '>=', $windowStart)
                ->get(['created_at'])
            : User::query()
                ->where('created_at', '>=', $windowStart)
                ->get(['created_at']);

        foreach ($rows as $row) {
            $monthKey = $row->created_at?->format('Y-m');

            if (! is_string($monthKey) || ! array_key_exists($monthKey, $countsByMonth)) {
                continue;
            }

            $countsByMonth[$monthKey]++;
        }

        $labels = $months
            ->map(
                fn (Carbon $month): string => Str::ucfirst(
                    $month->locale(app()->getLocale())->translatedFormat('M')
                )
            )
            ->values()
            ->all();

        /** @var list<int> $series */
        $series = array_values($countsByMonth);

        return [$labels, $series];
    }

    protected function percentage(int $part, int $total): float
    {
        if ($total <= 0) {
            return 0.0;
        }

        return round(($part / $total) * 100, 1);
    }
}
