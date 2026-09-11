<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\ImpersonationSessionController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChequePaymentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ReceptionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\TillController;
use App\Http\Controllers\TillClosureController;
use App\Http\Controllers\TillManagementController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\StockAdjustmentController;

Route::pattern('tenant', '[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?');

// Serve storage files directly to bypass Windows symlink issues
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    if (!file_exists($fullPath)) {
        abort(404);
    }
    return response()->file($fullPath);
})->where('path', '.*');

// Central first, so it wins on /admin/*. IdentifyTenant runs at the end of
// the web group (see bootstrap/app.php), so no per-route wiring is needed.
Route::prefix(config('tenancy.central_prefix'))
    ->name('central.')
    ->middleware(['central_only'])
    ->group(base_path('routes/central.php'));

Route::get('/', fn () => redirect('/'.config('tenancy.central_prefix').'/login'));

Route::prefix('{tenant}')
    ->group(function () {
        Route::get('/', LandingController::class)->name('tenant.home');

        // A minted impersonation token redeems the central session into a
        // tenant session — must sit OUTSIDE the `auth` group.
        Route::get('/impersonate/{token}', [ImpersonationSessionController::class, 'store']);

        Route::middleware('guest')->group(function () {
            Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
            Route::post('/login', [AuthController::class, 'login'])->name('login.perform');
        });

        Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::middleware('auth')->group(function () {
            // ==================== DATABASE MIGRATION ====================
            Route::get('/migrate', function () {
                abort_unless(auth()->user()->can('settings.access'), 403);

                Artisan::call('migrate', [
                    '--force' => true,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Database migration completed successfully.',
                    'output' => Artisan::output(),
                ]);
            })->name('migrate');
            // ==================== END DATABASE MIGRATION ====================

            // ==================== RECEPTION ====================
            Route::get('/reception', [ReceptionController::class, 'index'])
                ->name('reception.index')
                ->middleware('permission:reception.access');

            Route::post('/reception/search', [ReceptionController::class, 'search'])
                ->name('reception.search')
                ->middleware('permission:reception.access');

            Route::post('/reception/job', [ReceptionController::class, 'createJob'])
                ->name('reception.create-job')
                ->middleware('permission:reception.create_job');

            Route::get('/reception/services', [ReceptionController::class, 'getServices'])
                ->name('reception.services')
                ->middleware('permission:reception.access');

            Route::get('/reception/products', [ReceptionController::class, 'getProducts'])
                ->name('reception.products')
                ->middleware('permission:reception.access');

            Route::get('/reception/vehicle/{vehicle}/image', [ReceptionController::class, 'vehicleImage'])
                ->name('reception.vehicle-image')
                ->middleware('permission:reception.access');
            // ==================== END RECEPTION ====================

            // ==================== CUSTOMERS ====================
            Route::get('/customers/list', [CustomerController::class, 'list'])
                ->name('customers.list');

            // API endpoints for dropdowns
            Route::get('/customers/phone-numbers', [CustomerController::class, 'phoneNumbers'])
                ->name('customers.phone-numbers');

            Route::get('/customers/vehicles', [CustomerController::class, 'vehicles'])
                ->name('customers.vehicles');

            Route::get('/customers', [CustomerController::class, 'index'])
                ->name('customers.index')
                ->middleware('permission:customers.access');

            Route::get('/customers/create', [CustomerController::class, 'create'])
                ->name('customers.create')
                ->middleware('permission:customers.create');

            Route::post('/customers', [CustomerController::class, 'store'])
                ->name('customers.store')
                ->middleware('permission:customers.create');

            Route::get('/customers/{customer}', [CustomerController::class, 'show'])
                ->name('customers.show')
                ->middleware('permission:customers.access');

            Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])
                ->name('customers.edit')
                ->middleware('permission:customers.edit');

            Route::put('/customers/{customer}', [CustomerController::class, 'update'])
                ->name('customers.update')
                ->middleware('permission:customers.edit');

            Route::patch('/customers/{customer}', [CustomerController::class, 'update'])
                ->name('customers.update.patch')
                ->middleware('permission:customers.edit');

            Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])
                ->name('customers.destroy')
                ->middleware('permission:customers.delete');
            // ==================== END CUSTOMERS ====================

            // ==================== DASHBOARD ====================
            Route::get('/dashboard', [DashboardController::class, 'index'])
                ->name('dashboard')
                ->middleware('permission:dashboard.access');
            // ==================== END DASHBOARD ====================

            // ==================== VEHICLES (UPDATED - GRANULAR) ====================
            Route::get('/vehicles', [VehicleController::class, 'index'])
                ->name('vehicles.index')
                ->middleware('permission:vehicles.access');

            // API endpoints for dropdowns (must come before parameterized routes)
            Route::get('/vehicles/categories', [VehicleController::class, 'categories'])
                ->name('vehicles.categories');

            Route::get('/vehicles/list', [VehicleController::class, 'vehicleList'])
                ->name('vehicles.list');

            Route::get('/vehicles/create', [VehicleController::class, 'create'])
                ->name('vehicles.create')
                ->middleware('permission:vehicles.create');

            Route::post('/vehicles', [VehicleController::class, 'store'])
                ->name('vehicles.store')
                ->middleware('permission:vehicles.create');

            Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show'])
                ->name('vehicles.show')
                ->middleware('permission:vehicles.access');

            Route::get('/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])
                ->name('vehicles.edit')
                ->middleware('permission:vehicles.edit');

            Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])
                ->name('vehicles.update')
                ->middleware('permission:vehicles.edit');

            Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])
                ->name('vehicles.destroy')
                ->middleware('permission:vehicles.delete');

            Route::post('/vehicles/{vehicle}/transfer-ownership', [VehicleController::class, 'transferOwnership'])
                ->name('vehicles.transfer_ownership')
                ->middleware('permission:vehicles.transfer_ownership');
            // ==================== END VEHICLES ====================

            // ==================== APPOINTMENTS ====================
            Route::get('/appointments', [AppointmentController::class, 'index'])
                ->name('appointments.index')
                ->middleware('permission:appointments.access');

            Route::get('/appointments/create', [AppointmentController::class, 'create'])
                ->name('appointments.create')
                ->middleware('permission:appointments.create');

            Route::post('/appointments', [AppointmentController::class, 'store'])
                ->name('appointments.store')
                ->middleware('permission:appointments.create');

            Route::get('/appointments/{appointment}/edit', [AppointmentController::class, 'edit'])
                ->name('appointments.edit')
                ->middleware('permission:appointments.edit');

            Route::put('/appointments/{appointment}', [AppointmentController::class, 'update'])
                ->name('appointments.update')
                ->middleware('permission:appointments.edit');

            Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])
                ->name('appointments.destroy')
                ->middleware('permission:appointments.delete');
            // ==================== END APPOINTMENTS ====================

            // ==================== JOBS SECTION ====================
            Route::get('/jobs/board', [JobController::class, 'board'])
                ->name('jobs.board')
                ->middleware('permission:live_job_board.access');

            Route::get('/jobs', [JobController::class, 'index'])
                ->name('jobs.index')
                ->middleware('permission:job_cards.access');

            Route::get('/jobs/create', [JobController::class, 'create'])
                ->name('jobs.create')
                ->middleware('permission:job_cards.create');

            Route::post('/jobs', [JobController::class, 'store'])
                ->name('jobs.store')
                ->middleware('permission:job_cards.create');

            Route::get('/jobs/{job}', [JobController::class, 'show'])
                ->name('jobs.show')
                ->middleware('permission:job_cards.access');

            Route::get('/jobs/{job}/edit', [JobController::class, 'edit'])
                ->name('jobs.edit')
                ->middleware('permission:job_cards.edit');

            Route::put('/jobs/{job}', [JobController::class, 'update'])
                ->name('jobs.update')
                ->middleware('permission:job_cards.edit');

            Route::delete('/jobs/{job}', [JobController::class, 'destroy'])
                ->name('jobs.destroy')
                ->middleware('permission:job_cards.delete');

            Route::post('/jobs/{job}/status', [JobController::class, 'status'])
                ->name('jobs.status')
                ->middleware('permission:job_cards.change_status');

            Route::post('/jobs/{job}/additional-work', [JobController::class, 'additionalWork'])
                ->name('jobs.additional-work')
                ->middleware('permission:job_cards.request_additional_work');

            Route::post('/jobs/{job}/approve', [JobController::class, 'approve'])
                ->name('jobs.approve')
                ->middleware('permission:job_cards.approve');

            Route::post('/jobs/{job}/consume-part', [JobController::class, 'consumePart'])
                ->name('jobs.consume-part')
                ->middleware('permission:job_cards.consume_parts');

            Route::post('/jobs/{job}/parts/{part}/apply', [JobController::class, 'applyPart'])
                ->name('jobs.parts.apply')
                ->middleware('permission:job_cards.consume_parts');

            Route::delete('/jobs/{job}/parts/{part}/remove', [JobController::class, 'removePart'])
                ->name('jobs.parts.remove')
                ->middleware('permission:job_cards.consume_parts');

            Route::post('/jobs/{job}/services', [JobController::class, 'addService'])
                ->name('jobs.services.add')
                ->middleware('permission:job_cards.edit');

            Route::post('/jobs/{job}/services/{service}/apply', [JobController::class, 'applyService'])
                ->name('jobs.services.apply')
                ->middleware('permission:job_cards.edit');

            Route::delete('/jobs/{job}/services/{service}/remove', [JobController::class, 'removeService'])
                ->name('jobs.services.remove')
                ->middleware('permission:job_cards.edit');

            // Inspection
            Route::get('/jobs/{job}/inspection', [InspectionController::class, 'edit'])
                ->name('jobs.inspection.edit')
                ->middleware('permission:job_cards.edit_inspection');

            Route::post('/jobs/{job}/inspection', [InspectionController::class, 'update'])
                ->name('jobs.inspection.update')
                ->middleware('permission:job_cards.edit_inspection');
            // ==================== END JOBS SECTION ====================

            // ==================== INVENTORY ====================
            Route::get('/inventory', [InventoryController::class, 'index'])
                ->name('inventory.index')
                ->middleware('permission:inventory.access');

            Route::get('/inventory/create', [InventoryController::class, 'create'])
                ->name('inventory.create')
                ->middleware('permission:inventory.create');

            Route::post('/inventory', [InventoryController::class, 'store'])
                ->name('inventory.store')
                ->middleware('permission:inventory.create');

            // Live inventory status updates
            Route::get('/inventory/status', [InventoryController::class, 'getStatus'])
                ->name('inventory.status')
                ->middleware('permission:inventory.access');

            // API endpoints for dropdowns
            Route::get('/inventory/brands', [InventoryController::class, 'brands'])
                ->name('inventory.brands');

            Route::get('/inventory/products', [InventoryController::class, 'productList'])
                ->name('inventory.products');

            Route::get('/inventory/{product}/edit', [InventoryController::class, 'edit'])
                ->name('inventory.edit')
                ->middleware('permission:inventory.edit');

            Route::put('/inventory/{product}', [InventoryController::class, 'update'])
                ->name('inventory.update')
                ->middleware('permission:inventory.edit');

            Route::delete('/inventory/{product}', [InventoryController::class, 'destroy'])
                ->name('inventory.destroy')
                ->middleware('permission:inventory.delete');

            Route::post('/inventory/{product}/adjust', [InventoryController::class, 'adjust'])
                ->name('inventory.adjust')
                ->middleware('permission:inventory.adjust_stock');

            Route::get('/inventory/{product}/stock', [InventoryController::class, 'stock'])
                ->name('inventory.stock')
                ->middleware('permission:inventory.access');
            // ==================== END INVENTORY ====================

            // ==================== CATEGORIES ====================
            Route::get('/categories', [CategoryController::class, 'index'])
                ->name('categories.index')
                ->middleware('permission:categories.access');

            Route::get('/categories/list', [CategoryController::class, 'list'])
                ->name('categories.list');

            Route::get('/categories/create', [CategoryController::class, 'create'])
                ->name('categories.create')
                ->middleware('permission:categories.create');

            Route::post('/categories', [CategoryController::class, 'store'])
                ->name('categories.store')
                ->middleware('permission:categories.create');

            Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])
                ->name('categories.edit')
                ->middleware('permission:categories.edit');

            Route::put('/categories/{category}', [CategoryController::class, 'update'])
                ->name('categories.update')
                ->middleware('permission:categories.edit');

            Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
                ->name('categories.destroy')
                ->middleware('permission:categories.delete');
            // ==================== END CATEGORIES ====================

            // ==================== SERVICES ====================
            Route::get('/services', [ServiceController::class, 'index'])
                ->name('services.index')
                ->middleware('permission:services.access');

            Route::get('/services/create', [ServiceController::class, 'create'])
                ->name('services.create')
                ->middleware('permission:services.create');

            Route::post('/services', [ServiceController::class, 'store'])
                ->name('services.store')
                ->middleware('permission:services.create');

            Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])
                ->name('services.edit')
                ->middleware('permission:services.edit');

            Route::put('/services/{service}', [ServiceController::class, 'update'])
                ->name('services.update')
                ->middleware('permission:services.edit');

            Route::delete('/services/{service}', [ServiceController::class, 'destroy'])
                ->name('services.destroy')
                ->middleware('permission:services.delete');

            Route::post('/services/{service}/toggle', [ServiceController::class, 'toggle'])
                ->name('services.toggle')
                ->middleware('permission:services.toggle');
            // ==================== END SERVICES ====================

            // ==================== INVOICES (UPDATED) ====================
            Route::get('/invoices', [InvoiceController::class, 'index'])
                ->name('invoices.index')
                ->middleware('permission:invoices.access');

            Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])
                ->name('invoices.show')
                ->middleware('permission:invoices.access');

            Route::post('/invoices/{invoice}/pay', [InvoiceController::class, 'pay'])
                ->name('invoices.pay')
                ->middleware('permission:invoices.pay');

            Route::get('/invoices/{invoice}/print/{format?}', [InvoiceController::class, 'printInvoice'])
                ->name('invoices.print')
                ->middleware('permission:invoices.print')
                ->where('format', 'a4|thermal');
            // ==================== END INVOICES ====================

            // ==================== CASHIER (UPDATED) ====================
            Route::get('/cashier', [CashierController::class, 'index'])
                ->name('cashier.index')
                ->middleware('permission:cashier.access');

            // ==================== NOTIFICATIONS ====================
            Route::get('/notifications', [NotificationController::class, 'index'])
                ->name('notifications.index')
                ->middleware('permission:cashier.access');
            // ==================== END NOTIFICATIONS ====================

            Route::get('/cashier/search', [CashierController::class, 'search'])
                ->name('cashier.search')
                ->middleware('permission:cashier.search');

            Route::get('/cashier/payment/{job}', [CashierController::class, 'payment'])
                ->name('cashier.payment')
                ->middleware('permission:cashier.payment');

            Route::post('/cashier/payment/{job}', [CashierController::class, 'processPayment'])
                ->name('cashier.process-payment')
                ->middleware('permission:cashier.payment');

            Route::get('/cashier/print-options/{job}', [CashierController::class, 'printOptions'])
                ->name('cashier.print-options')
                ->middleware('permission:cashier.print_options');

            Route::post('/cashier/cash-in', [CashierController::class, 'cashIn'])
                ->name('cashier.cash-in')
                ->middleware('permission:cashier.cash_in');

            Route::post('/cashier/cash-out', [CashierController::class, 'cashOut'])
                ->name('cashier.cash-out')
                ->middleware('permission:cashier.cash_out');

            Route::post('/cashier/cash-drop', [CashierController::class, 'cashDrop'])
                ->name('cashier.cash-drop')
                ->middleware('permission:cashier.cash_drop');

            Route::get('/cashier/till-action', [TillClosureController::class, 'showTillAction'])
                ->name('cashier.till-action')
                ->middleware('permission:cashier.open_shift');

            Route::post('/cashier/till-action', [TillClosureController::class, 'handleTillAction'])
                ->name('cashier.till-action.post')
                ->middleware('permission:cashier.open_shift');

            Route::get('/cashier/shift/history', [TillClosureController::class, 'history'])
                ->name('cashier.shift-history')
                ->middleware('permission:cashier.access');

            Route::get('/cashier/shift/{closure}', [TillClosureController::class, 'show'])
                ->name('cashier.shift-show')
                ->middleware('permission:cashier.access');
            // ==================== END CASHIER ====================

            // ==================== CHEQUE PAYMENTS ====================
            Route::get('/cheque-payments', [ChequePaymentController::class, 'index'])
                ->name('cheque-payments.index')
                ->middleware('permission:cashier.access');

            Route::get('/cheque-payments/{payment}/confirm', [ChequePaymentController::class, 'confirm'])
                ->name('cheque-payments.confirm')
                ->middleware('permission:cashier.access');

            Route::post('/cheque-payments/{payment}/process', [ChequePaymentController::class, 'processConfirmation'])
                ->name('cheque-payments.process')
                ->middleware('permission:cashier.access');

            Route::get('/cheque-payments/{payment}', [ChequePaymentController::class, 'show'])
                ->name('cheque-payments.show')
                ->middleware('permission:cashier.access');

            Route::get('/cheque-payments/{payment}/edit-bounce', [ChequePaymentController::class, 'editBounce'])
                ->name('cheque-payments.edit-bounce')
                ->middleware('permission:cashier.access');

            Route::post('/cheque-payments/{payment}/update-bounce', [ChequePaymentController::class, 'updateBounce'])
                ->name('cheque-payments.update-bounce')
                ->middleware('permission:cashier.access');

            Route::get('/cheque-payments/{payment}/replacement', [ChequePaymentController::class, 'showReplacementForm'])
                ->name('cheque-payments.replacement')
                ->middleware('permission:cashier.access');

            Route::post('/cheque-payments/{payment}/process-replacement', [ChequePaymentController::class, 'processReplacement'])
                ->name('cheque-payments.process-replacement')
                ->middleware('permission:cashier.access');
            // ==================== END CHEQUE PAYMENTS ====================

            // ==================== REPORTS (UPDATED) ====================
            Route::get('/reports', [ReportController::class, 'index'])
                ->name('reports')
                ->middleware('permission:reports.access');

            Route::get('/reports/sales', [ReportController::class, 'salesReport'])
                ->name('reports.sales')
                ->middleware('permission:reports.sales');

            Route::get('/reports/stock', [ReportController::class, 'stockReport'])
                ->name('reports.stock')
                ->middleware('permission:reports.stock');

            Route::get('/reports/stock-movement', [ReportController::class, 'stockMovementReport'])
                ->name('reports.stock-movement')
                ->middleware('permission:reports.stock_movement');

            Route::get('/reports/services', [ReportController::class, 'serviceReport'])
                ->name('reports.services')
                ->middleware('permission:reports.services');

            Route::get('/reports/customers', [ReportController::class, 'customerReport'])
                ->name('reports.customers')
                ->middleware('permission:reports.customers');

            Route::get('/reports/cash-movements', [ReportController::class, 'cashMovementsReport'])
                ->name('reports.cash-movements')
                ->middleware('permission:cash_movements.access');
            // ==================== END REPORTS ====================

            // ==================== USERS ====================
            Route::get('/users', [UserController::class, 'index'])
                ->name('users.index')
                ->middleware('permission:users.access');

            Route::get('/users/create', [UserController::class, 'create'])
                ->name('users.create')
                ->middleware('permission:users.create');

            Route::post('/users', [UserController::class, 'store'])
                ->name('users.store')
                ->middleware('permission:users.create');

            Route::get('/users/{user}/edit', [UserController::class, 'edit'])
                ->name('users.edit')
                ->middleware('permission:users.edit');

            Route::put('/users/{user}', [UserController::class, 'update'])
                ->name('users.update')
                ->middleware('permission:users.edit');

            Route::patch('/users/{user}', [UserController::class, 'update'])
                ->name('users.update.patch')
                ->middleware('permission:users.edit');

            Route::delete('/users/{user}', [UserController::class, 'destroy'])
                ->name('users.destroy')
                ->middleware('permission:users.delete');
            // ==================== END USERS ====================

            // ==================== ROLES ====================
            Route::get('/roles', [RoleController::class, 'index'])
                ->name('roles.index')
                ->middleware('permission:roles.access');

            Route::get('/roles/create', [RoleController::class, 'create'])
                ->name('roles.create')
                ->middleware('permission:roles.create');

            Route::post('/roles', [RoleController::class, 'store'])
                ->name('roles.store')
                ->middleware('permission:roles.create');

            Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])
                ->name('roles.edit')
                ->middleware('permission:roles.edit');

            Route::put('/roles/{role}', [RoleController::class, 'update'])
                ->name('roles.update')
                ->middleware('permission:roles.edit');

            Route::patch('/roles/{role}', [RoleController::class, 'update'])
                ->name('roles.update.patch')
                ->middleware('permission:roles.edit');

            Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
                ->name('roles.destroy')
                ->middleware('permission:roles.delete');
            // ==================== END ROLES ====================

            // ==================== SERVICE CATEGORIES ====================
            Route::get('/service-categories/list', [ServiceCategoryController::class, 'list'])
                ->name('service-categories.list');
            Route::post('/service-categories', [ServiceCategoryController::class, 'store'])
                ->name('service-categories.store')
                ->middleware('permission:services.create');
            // ==================== END SERVICE CATEGORIES ====================

            // ==================== STOCK ADJUSTMENTS ====================
            Route::get('/stock-adjustments', [StockAdjustmentController::class, 'index'])
                ->name('stock-adjustments.index')
                ->middleware('permission:stock_adjustments.access');

            Route::post('/stock-adjustments', [StockAdjustmentController::class, 'store'])
                ->name('stock-adjustments.store')
                ->middleware('permission:stock_adjustments.create');

            Route::post('/stock-adjustments/{stockAdjustment}/reverse', [StockAdjustmentController::class, 'reverse'])
                ->name('stock-adjustments.reverse')
                ->middleware('permission:stock_adjustments.reverse');
            // ==================== END STOCK ADJUSTMENTS ====================

            // ==================== TILL MANAGEMENT ====================
            Route::get('/tills', [TillManagementController::class, 'index'])
                ->name('tills.index')
                ->middleware('permission:settings.access');

            Route::get('/tills/create', [TillManagementController::class, 'create'])
                ->name('tills.create')
                ->middleware('permission:settings.access');

            Route::post('/tills', [TillManagementController::class, 'store'])
                ->name('tills.store')
                ->middleware('permission:settings.access');

            // Specific routes must come before dynamic routes
            Route::get('/tills/status', [TillManagementController::class, 'getTillStatus'])
                ->name('tills.status')
                ->middleware('permission:cashier.access');

            Route::post('/tills/select', [TillManagementController::class, 'selectTill'])
                ->name('tills.select')
                ->middleware('permission:cashier.access');

            Route::post('/tills/clear', [TillManagementController::class, 'clearTillSelection'])
                ->name('tills.clear')
                ->middleware('permission:cashier.access');

            Route::get('/tills/user-assignments', [TillManagementController::class, 'userTillAssignments'])
                ->name('tills.user-assignments')
                ->middleware('permission:settings.access');

            Route::post('/tills/update-user-assignment', [TillManagementController::class, 'updateUserTillAssignment'])
                ->name('tills.update-user-assignment')
                ->middleware('permission:settings.access');

            Route::post('/tills/clear-admin-permanent-tills', [TillManagementController::class, 'clearAdminPermanentTills'])
                ->name('tills.clear-admin-permanent-tills')
                ->middleware('permission:settings.access');

            Route::get('/tills/{till}', [TillManagementController::class, 'show'])
                ->name('tills.show')
                ->middleware('permission:settings.access');

            Route::get('/tills/{till}/edit', [TillManagementController::class, 'edit'])
                ->name('tills.edit')
                ->middleware('permission:settings.access');

            Route::put('/tills/{till}', [TillManagementController::class, 'update'])
                ->name('tills.update')
                ->middleware('permission:settings.access');

            Route::delete('/tills/{till}', [TillManagementController::class, 'destroy'])
                ->name('tills.destroy')
                ->middleware('permission:settings.access');
            // ==================== END TILL MANAGEMENT ====================

            // ==================== AUDIT LOGS ====================
            Route::get('/audit-logs', [AuditController::class, 'index'])
                ->name('audit-logs.index')
                ->middleware('permission:audit_logs.access');

            Route::get('/audit-logs/{id}', [AuditController::class, 'show'])
                ->name('audit-logs.show')
                ->middleware('permission:audit_logs.access')
                ->where('id', '[0-9]+');
            // ==================== END AUDIT LOGS ====================

            // ==================== SETTINGS ====================
            Route::get('/settings/till', [TillController::class, 'edit'])
                ->name('settings.till')
                ->middleware('permission:settings.access');

            Route::put('/settings/till', [TillController::class, 'update'])
                ->name('settings.till.update')
                ->middleware('permission:settings.access');
            // ==================== END SETTINGS ====================
        });
    });