<?php

use App\Http\Controllers\Central\AuthenticatedSessionController;
use App\Http\Controllers\Central\CentralAdminController;
use App\Http\Controllers\Central\CentralDashboardController;
use App\Http\Controllers\Central\DeployController;
use App\Http\Controllers\Central\ImpersonationController;
use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\Central\TenantModuleController;
use App\Http\Controllers\Central\TenantSettingController;
use App\Http\Controllers\Central\TestInstanceController;
use Illuminate\Support\Facades\Route;

// Mounted under {central_prefix} with the `central.` name prefix — see
// routes/web.php. Every route below is behind `central_only` (EnsureCentralContext).

Route::get('/login', [AuthenticatedSessionController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'login'])->name('login.perform')->middleware('throttle:5,1');

// artisan-over-HTTP for hosts with no SSH/Terminal (see DEPLOY.txt). Outside
// auth:central on purpose — the first deploy runs before any central admin
// account exists. Guarded instead by DEPLOY_SECRET (DeployController 404s
// without it); throttled to slow down secret-guessing.
Route::get('/deploy/{action}', [DeployController::class, 'run'])
    ->name('deploy.run')
    ->middleware('throttle:10,1');

// Serve storage files for central context (for logos, etc.)
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    if (!file_exists($fullPath)) {
        abort(404);
    }
    return response()->file($fullPath);
})->where('path', '.*');

Route::middleware(['auth:central'])->group(function () {
    // Restore the ambient default guard — `auth:central` repoints every
    // unguarded Auth::* call at the CentralAdmin (see
    // ResetDefaultGuardAfterCentralAuth).
    Route::middleware(['central_reset_guard'])->group(function () {
        Route::post('/logout', [AuthenticatedSessionController::class, 'logout'])->name('logout');

        Route::get('/', [CentralDashboardController::class, 'index'])->name('dashboard');

        Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
        Route::get('/tenants/create', [TenantController::class, 'create'])->name('tenants.create');
        Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
        Route::get('/tenants/{tenant}', [TenantController::class, 'show'])->name('tenants.show');
        Route::post('/tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.update');
        Route::post('/tenants/{tenant}/suspend', [TenantController::class, 'suspend'])->name('tenants.suspend');
        Route::post('/tenants/{tenant}/resume', [TenantController::class, 'resume'])->name('tenants.resume');
        Route::post('/tenants/{tenant}/reset-admin-password', [TenantController::class, 'resetAdminPassword'])->name('tenants.reset-admin-password');
        Route::get('/tenants/{tenant}/audit', [TenantController::class, 'auditLogs'])->name('tenants.audit');

        Route::get('/tenants/{tenant}/settings', [TenantSettingController::class, 'index'])->name('tenants.settings');
        Route::post('/tenants/{tenant}/settings', [TenantSettingController::class, 'update'])->name('tenants.settings.update');

        Route::get('/tenants/{tenant}/modules', [TenantModuleController::class, 'index'])->name('tenants.modules');
        Route::post('/tenants/{tenant}/modules/{moduleKey}', [TenantModuleController::class, 'update'])->name('tenants.modules.update');

        Route::get('/tenants/{tenant}/tills', [TenantController::class, 'tills'])->name('tenants.tills');
        Route::post('/tenants/{tenant}/tills', [TenantController::class, 'createTill'])->name('tenants.tills.create');

        Route::post('/tenants/{tenant}/impersonate', [ImpersonationController::class, 'store'])->name('tenants.impersonate');

        Route::post('/tenants/{tenant}/test-instance', [TestInstanceController::class, 'create'])->name('tenants.test-instance.create');
        Route::post('/tenants/{tenant}/test-instance/sync', [TestInstanceController::class, 'sync'])->name('tenants.test-instance.sync');
        Route::post('/tenants/{tenant}/test-instance/destroy', [TestInstanceController::class, 'destroy'])->name('tenants.test-instance.destroy');

        Route::get('/admins', [CentralAdminController::class, 'index'])->name('admins.index');
        Route::post('/admins', [CentralAdminController::class, 'store'])->name('admins.store');
        Route::patch('/admins/{admin}', [CentralAdminController::class, 'update'])->name('admins.update');
        Route::delete('/admins/{admin}', [CentralAdminController::class, 'destroy'])->name('admins.destroy');
    });
});
