<?php

namespace App\Providers;

use App\Models\User;
use App\Support\Authorization\PermissionCatalog;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(TenantContext::class, fn () => new TenantContext());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::defaults(['locale' => app()->getLocale()]);

        Gate::before(function (User $user, string $ability) {
            if ((bool) $user->is_superadmin) {
                return true;
            }

            if (in_array($ability, PermissionCatalog::names(), true)) {
                return $user->hasTenantPermission($ability);
            }

            return null;
        });
    }
}
