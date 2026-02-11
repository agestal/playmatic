<?php

use App\Http\Controllers\Access\TenantRoleController;
use App\Http\Controllers\Access\TenantPermissionController;
use App\Http\Controllers\Access\TenantUserAccessController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Games\GameController;
use App\Http\Controllers\Games\GameEntryController;
use App\Http\Controllers\Games\GameWinnerController;
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
            Route::get('/', DashboardController::class)->name('dashboard');

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

            Route::prefix('games')->name('games.')->group(function () {
                Route::get('/', [GameController::class, 'index'])
                    ->middleware('tenant.permission:games.view.entity')
                    ->name('index');
                Route::get('/create', [GameController::class, 'create'])
                    ->middleware('tenant.permission:games.edit.entity')
                    ->name('create');
                Route::post('/', [GameController::class, 'store'])
                    ->middleware('tenant.permission:games.edit.entity')
                    ->name('store');
                Route::get('/{game}/edit', [GameController::class, 'edit'])
                    ->middleware('tenant.permission:games.edit.entity')
                    ->name('edit');
                Route::put('/{game}', [GameController::class, 'update'])
                    ->middleware('tenant.permission:games.edit.entity')
                    ->name('update');
                Route::delete('/{game}', [GameController::class, 'destroy'])
                    ->middleware('tenant.permission:games.edit.entity')
                    ->name('destroy');

                Route::prefix('entries')->name('entries.')->group(function () {
                    Route::get('/', [GameEntryController::class, 'index'])
                        ->middleware('tenant.permission:participants.view.entity')
                        ->name('index');
                    Route::get('/create', [GameEntryController::class, 'create'])
                        ->middleware('tenant.permission:games.edit.content')
                        ->name('create');
                    Route::post('/', [GameEntryController::class, 'store'])
                        ->middleware('tenant.permission:games.edit.content')
                        ->name('store');
                    Route::get('/{entry}/edit', [GameEntryController::class, 'edit'])
                        ->middleware('tenant.permission:games.edit.content')
                        ->name('edit');
                    Route::put('/{entry}', [GameEntryController::class, 'update'])
                        ->middleware('tenant.permission:games.edit.content')
                        ->name('update');
                    Route::delete('/{entry}', [GameEntryController::class, 'destroy'])
                        ->middleware('tenant.permission:games.edit.content')
                        ->name('destroy');
                });

                Route::prefix('winners')->name('winners.')->group(function () {
                    Route::get('/', [GameWinnerController::class, 'index'])
                        ->middleware('tenant.permission:winners.view.entity')
                        ->name('index');
                    Route::get('/create', [GameWinnerController::class, 'create'])
                        ->middleware('tenant.permission:games.edit.content')
                        ->name('create');
                    Route::post('/', [GameWinnerController::class, 'store'])
                        ->middleware('tenant.permission:games.edit.content')
                        ->name('store');
                    Route::get('/{winner}/edit', [GameWinnerController::class, 'edit'])
                        ->middleware('tenant.permission:games.edit.content')
                        ->name('edit');
                    Route::put('/{winner}', [GameWinnerController::class, 'update'])
                        ->middleware('tenant.permission:games.edit.content')
                        ->name('update');
                    Route::delete('/{winner}', [GameWinnerController::class, 'destroy'])
                        ->middleware('tenant.permission:games.edit.content')
                        ->name('destroy');
                });
            });

            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        });

        require __DIR__.'/auth.php';
    });
