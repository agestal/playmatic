<?php

use App\Http\Controllers\Access\TenantRoleController;
use App\Http\Controllers\Access\TenantPermissionController;
use App\Http\Controllers\Access\TenantUserAccessController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Games\AttendanceGuessRoundController;
use App\Http\Controllers\Games\GameController;
use App\Http\Controllers\Games\GameEntryController;
use App\Http\Controllers\Games\GameWinnerController;
use App\Http\Controllers\Games\PublicAttendanceGuessController;
use App\Http\Controllers\Games\QuizAnswerController;
use App\Http\Controllers\Games\QuizQuestionController;
use App\Http\Controllers\Platform\TenantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Setup\InstallationController;
use Illuminate\Support\Facades\Route;

/**
 * Add new locales here (slug -> resources/lang/{slug}.json).
 */
$supportedLocales = ['en', 'es', 'pt'];

Route::get('/', function () use ($supportedLocales) {
    $defaultLocale = config('app.locale');

    if (! in_array($defaultLocale, $supportedLocales, true)) {
        $defaultLocale = $supportedLocales[0];
    }

    return redirect('/'.$defaultLocale);
});

Route::get('/adivina-aforo', [PublicAttendanceGuessController::class, 'show'])
    ->name('public.attendance-guess.show');
Route::post('/adivina-aforo', [PublicAttendanceGuessController::class, 'store'])
    ->name('public.attendance-guess.store');

Route::prefix('{locale}')
    ->whereIn('locale', $supportedLocales)
    ->middleware('set.locale')
    ->group(function () {
        Route::get('/install', [InstallationController::class, 'show'])->name('install.show');
        Route::post('/install', [InstallationController::class, 'store'])->name('install.store');

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
                Route::get('/users/{tenantUser}/edit', [TenantUserAccessController::class, 'edit'])->name('users.edit');
                Route::put('/users/{tenantUser}', [TenantUserAccessController::class, 'update'])->name('users.update');
                Route::put('/users/{tenantUser}/password', [TenantUserAccessController::class, 'updatePassword'])->name('users.password.update');
                Route::delete('/users/{tenantUser}', [TenantUserAccessController::class, 'destroy'])->name('users.destroy');
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

                Route::prefix('attendance-guess/rounds')->name('attendance-rounds.')->group(function () {
                    Route::get('/settings', [AttendanceGuessRoundController::class, 'settings'])
                        ->middleware('tenant.permission:games.view.entity')
                        ->name('settings.edit');
                    Route::get('/', [AttendanceGuessRoundController::class, 'index'])
                        ->middleware('tenant.permission:games.view.entity')
                        ->name('index');
                    Route::post('/settings', [AttendanceGuessRoundController::class, 'updateSettings'])
                        ->middleware('tenant.permission:games.edit.content')
                        ->name('settings.update');
                    Route::get('/{round}/export', [AttendanceGuessRoundController::class, 'export'])
                        ->middleware('tenant.permission:games.view.entity')
                        ->name('export');
                    Route::get('/create', [AttendanceGuessRoundController::class, 'create'])
                        ->middleware('tenant.permission:games.edit.content')
                        ->name('create');
                    Route::post('/', [AttendanceGuessRoundController::class, 'store'])
                        ->middleware('tenant.permission:games.edit.content')
                        ->name('store');
                    Route::get('/{round}/edit', [AttendanceGuessRoundController::class, 'edit'])
                        ->middleware('tenant.permission:games.edit.content')
                        ->name('edit');
                    Route::put('/{round}', [AttendanceGuessRoundController::class, 'update'])
                        ->middleware('tenant.permission:games.edit.content')
                        ->name('update');
                    Route::post('/{round}/activate', [AttendanceGuessRoundController::class, 'activate'])
                        ->middleware('tenant.permission:games.edit.content')
                        ->name('activate');
                    Route::post('/{round}/deactivate', [AttendanceGuessRoundController::class, 'deactivate'])
                        ->middleware('tenant.permission:games.edit.content')
                        ->name('deactivate');
                    Route::post('/{round}/generate-winners', [AttendanceGuessRoundController::class, 'generateWinners'])
                        ->middleware('tenant.permission:games.edit.content')
                        ->name('generate-winners');
                    Route::post('/{round}/reset-winners', [AttendanceGuessRoundController::class, 'resetWinners'])
                        ->middleware('tenant.permission:games.edit.content')
                        ->name('reset-winners');
                    Route::delete('/{round}', [AttendanceGuessRoundController::class, 'destroy'])
                        ->middleware('tenant.permission:games.edit.content')
                        ->name('destroy');
                });

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

                Route::prefix('quiz/questions')
                    ->name('quiz.questions.')
                    ->middleware('tenant.game:trivial')
                    ->group(function () {
                        Route::get('/', [QuizQuestionController::class, 'index'])
                            ->middleware('tenant.permission:games.view.content')
                            ->name('index');
                        Route::get('/create', [QuizQuestionController::class, 'create'])
                            ->middleware('tenant.permission:games.edit.content')
                            ->name('create');
                        Route::post('/', [QuizQuestionController::class, 'store'])
                            ->middleware('tenant.permission:games.edit.content')
                            ->name('store');
                        Route::get('/{question}/edit', [QuizQuestionController::class, 'edit'])
                            ->middleware('tenant.permission:games.edit.content')
                            ->name('edit');
                        Route::put('/{question}', [QuizQuestionController::class, 'update'])
                            ->middleware('tenant.permission:games.edit.content')
                            ->name('update');
                        Route::delete('/{question}', [QuizQuestionController::class, 'destroy'])
                            ->middleware('tenant.permission:games.edit.content')
                            ->name('destroy');
                    });

                Route::prefix('quiz/answers')
                    ->name('quiz.answers.')
                    ->middleware('tenant.game:trivial')
                    ->group(function () {
                        Route::get('/', [QuizAnswerController::class, 'index'])
                            ->middleware('tenant.permission:games.view.content')
                            ->name('index');
                        Route::get('/create', [QuizAnswerController::class, 'create'])
                            ->middleware('tenant.permission:games.edit.content')
                            ->name('create');
                        Route::post('/', [QuizAnswerController::class, 'store'])
                            ->middleware('tenant.permission:games.edit.content')
                            ->name('store');
                        Route::get('/{answer}/edit', [QuizAnswerController::class, 'edit'])
                            ->middleware('tenant.permission:games.edit.content')
                            ->name('edit');
                        Route::put('/{answer}', [QuizAnswerController::class, 'update'])
                            ->middleware('tenant.permission:games.edit.content')
                            ->name('update');
                        Route::delete('/{answer}', [QuizAnswerController::class, 'destroy'])
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
