<?php

use App\Http\Controllers\Access\TenantRoleController;
use App\Http\Controllers\Access\TenantPermissionController;
use App\Http\Controllers\Access\TenantUserAccessController;
use App\Http\Controllers\Platform\TenantController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/**
 * Add new locales here (slug -> resources/lang/{slug}.json).
 */
$supportedLocales = ['en', 'es'];

Route::get('/', function () use ($supportedLocales) {
    $defaultLocale = config('app.locale');

    if (! in_array($defaultLocale, $supportedLocales, true)) {
        $defaultLocale = $supportedLocales[0];
    }

    return redirect('/'.$defaultLocale);
});

Route::prefix('{locale}')
    ->whereIn('locale', $supportedLocales)
    ->middleware('set.locale')
    ->group(function () {
        Route::middleware(['auth', 'superadmin'])
            ->prefix('platform')
            ->name('platform.')
            ->group(function () {
                Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
                Route::get('/tenants/create', [TenantController::class, 'create'])->name('tenants.create');
                Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
                Route::get('/tenants/{tenant}/edit', [TenantController::class, 'edit'])->name('tenants.edit');
                Route::put('/tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.update');
                Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy');
            });

        Route::middleware(['auth', 'tenant.member'])->group(function () {
            Route::get('/', fn () => view('app.dashboard'))->name('dashboard');

            Route::middleware('tenant.permission:tenant.users.manage')->group(function () {
                Route::get('/users', [TenantUserAccessController::class, 'index'])->name('users.index');
                Route::post('/users', [TenantUserAccessController::class, 'store'])->name('users.store');
                Route::delete('/users/bulk', [TenantUserAccessController::class, 'destroyMany'])->name('users.destroy-many');
                Route::put('/users/{tenantUser}', [TenantUserAccessController::class, 'update'])->name('users.update');
                Route::delete('/users/{tenantUser}', [TenantUserAccessController::class, 'destroy'])->name('users.destroy');
                Route::get('/users/{user}/edit', fn () => view('users.edit'))->name('users.edit');
            });

            Route::prefix('access')->name('access.')->group(function () {
                Route::middleware('tenant.permission:tenant.roles.manage')->group(function () {
                    Route::get('/roles', [TenantRoleController::class, 'index'])->name('roles.index');
                    Route::get('/roles/create', [TenantRoleController::class, 'create'])->name('roles.create');
                    Route::post('/roles', [TenantRoleController::class, 'store'])->name('roles.store');
                    Route::get('/roles/{role}/edit', [TenantRoleController::class, 'edit'])->name('roles.edit');
                    Route::put('/roles/{role}', [TenantRoleController::class, 'update'])->name('roles.update');
                    Route::delete('/roles/{role}', [TenantRoleController::class, 'destroy'])->name('roles.destroy');
                    Route::get('/permissions', [TenantPermissionController::class, 'index'])->name('permissions.index');
                });
            });

            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        });

        require __DIR__.'/auth.php';
    });
