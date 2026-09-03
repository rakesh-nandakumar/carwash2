<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReceptionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceCategoryController;

Route::get('/', function(){
    if(!auth()->check()) return redirect()->route('login');

    $user = auth()->user();
    // Redirect based on user permissions
    if($user->hasPermission('view_reception')) return redirect()->route('reception.index');
    if($user->hasPermission('view_dashboard')) return redirect()->route('dashboard');
    if($user->hasPermission('view_job_cards')) return redirect()->route('jobs.index');
    if($user->hasPermission('view_customers')) return redirect()->route('customers.index');

    // Default fallback
    return redirect()->route('dashboard');
});
Route::middleware('guest')->group(function(){
 Route::get('/login',[AuthController::class,'showLogin'])->name('login');
 Route::post('/login',[AuthController::class,'login'])->name('login.perform');
});
Route::get('/logout',[AuthController::class,'logout'])->name('logout');
Route::middleware('auth')->group(function(){
 Route::get('/reception',[ReceptionController::class,'index'])->name('reception.index')->middleware('permission:view_reception');
 Route::post('/reception/search',[ReceptionController::class,'search'])->name('reception.search')->middleware('permission:view_reception');
 Route::post('/reception/job',[ReceptionController::class,'createJob'])->name('reception.create-job')->middleware('permission:create_job_cards');
 Route::get('/reception/services',[ReceptionController::class,'getServices'])->name('reception.services')->middleware('permission:view_reception');
 Route::get('/reception/products',[ReceptionController::class,'getProducts'])->name('reception.products')->middleware('permission:view_reception');
 Route::get('/reception/vehicle/{vehicle}/image',[ReceptionController::class,'vehicleImage'])->name('reception.vehicle-image')->middleware('permission:view_reception');
 Route::get('/customers/list',[CustomerController::class,'list'])->name('customers.list')->middleware('permission:view_customers');
 Route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard')->middleware('permission:view_dashboard');
 Route::resource('customers',CustomerController::class)->middleware('permission:view_customers');
 Route::resource('vehicles',VehicleController::class)->middleware('permission:view_vehicles');
 Route::post('/vehicles/{vehicle}/transfer-ownership',[VehicleController::class,'transferOwnership'])->name('vehicles.transfer_ownership')->middleware('permission:view_vehicles');
 Route::resource('appointments',AppointmentController::class)->middleware('permission:view_appointments');
 Route::get('/jobs/board',[JobController::class,'board'])->name('jobs.board')->middleware('permission:view_live_job_board');
 Route::resource('jobs',JobController::class)->middleware('permission:view_job_cards');
 Route::get('/jobs/{job}/inspection',[InspectionController::class,'edit'])->name('jobs.inspection.edit')->middleware('permission:edit_inspection_job_cards');
 Route::post('/jobs/{job}/inspection',[InspectionController::class,'update'])->name('jobs.inspection.update')->middleware('permission:edit_inspection_job_cards');
 Route::post('/jobs/{job}/status',[JobController::class,'status'])->name('jobs.status')->middleware('permission:change_status_job_cards');
 Route::post('/jobs/{job}/additional-work',[JobController::class,'additionalWork'])->name('jobs.additional-work')->middleware('permission:request_additional_work_job_cards');
 Route::post('/jobs/{job}/approve',[JobController::class,'approve'])->name('jobs.approve')->middleware('permission:approve_job_cards');
 Route::post('/jobs/{job}/consume-part',[JobController::class,'consumePart'])->name('jobs.consume-part')->middleware('permission:consume_parts_job_cards');
Route::post('/jobs/{job}/parts/{part}/apply',[JobController::class,'applyPart'])->name('jobs.parts.apply')->middleware('permission:consume_parts_job_cards');
Route::delete('/jobs/{job}/parts/{part}/remove',[JobController::class,'removePart'])->name('jobs.parts.remove')->middleware('permission:consume_parts_job_cards');
Route::post('/jobs/{job}/services/{service}/apply',[JobController::class,'applyService'])->name('jobs.services.apply')->middleware('permission:consume_parts_job_cards');
Route::delete('/jobs/{job}/services/{service}/remove',[JobController::class,'removeService'])->name('jobs.services.remove')->middleware('permission:consume_parts_job_cards');
Route::post('/jobs/{job}/services',[JobController::class,'addService'])->name('jobs.services.add')->middleware('permission:consume_parts_job_cards');
 Route::resource('inventory',InventoryController::class)->except(['show'])->parameters(['inventory'=>'product'])->middleware('permission:view_item_master');
 Route::post('/inventory/{product}/adjust',[InventoryController::class,'adjust'])->name('inventory.adjust')->middleware('permission:adjust_stock_item_master');
 Route::resource('categories',CategoryController::class)->middleware('permission:view_categories');

 // ← add these two lines (the resource creates services.index, services.create, etc.)
 Route::resource('services', ServiceController::class)->middleware('permission:view_services');
 Route::post('/services/{service}/toggle',[ServiceController::class,'toggle'])->name('services.toggle')->middleware('permission:view_services');

 Route::resource('invoices',InvoiceController::class)->only(['index','show'])->middleware('permission:view_invoices');
 Route::post('/invoices/{invoice}/pay',[InvoiceController::class,'pay'])->name('invoices.pay')->middleware('permission:pay_invoices');
 Route::get('/invoices/{invoice}/print/{format?}',[InvoiceController::class,'printInvoice'])->name('invoices.print')->middleware('permission:print_invoices')->where('format','a4|thermal');
 Route::get('/cashier',[CashierController::class,'index'])->name('cashier.index')->middleware('permission:view_cashier');
 Route::get('/cashier/search',[CashierController::class,'search'])->name('cashier.search')->middleware('permission:search_cashier');
 Route::get('/cashier/payment/{job}',[CashierController::class,'payment'])->name('cashier.payment')->middleware('permission:payment_cashier');
 Route::post('/cashier/payment/{job}',[CashierController::class,'processPayment'])->name('cashier.process-payment')->middleware('permission:payment_cashier');
 Route::get('/cashier/print-options/{job}',[CashierController::class,'printOptions'])->name('cashier.print-options')->middleware('permission:print_options_cashier');
 Route::get('/reports',[ReportController::class,'index'])->name('reports')->middleware('permission:view_reports');
 Route::get('/reports/sales',[ReportController::class,'salesReport'])->name('reports.sales')->middleware('permission:view_reports');
 Route::get('/reports/stock',[ReportController::class,'stockReport'])->name('reports.stock')->middleware('permission:view_reports');
 Route::get('/reports/stock-movement',[ReportController::class,'stockMovementReport'])->name('reports.stock-movement')->middleware('permission:view_reports');
 Route::get('/reports/services',[ReportController::class,'serviceReport'])->name('reports.services')->middleware('permission:view_reports');
 Route::get('/reports/customers',[ReportController::class,'customerReport'])->name('reports.customers')->middleware('permission:view_reports');
 Route::get('/settings/billing',[SettingsController::class,'billing'])->name('settings.billing')->middleware('permission:view_settings');
 Route::get('/settings/reception',function(){return redirect()->route('settings.billing');})->name('settings.reception');
 Route::post('/settings/billing',[SettingsController::class,'updateBilling'])->name('settings.billing.update')->middleware('permission:edit_billing_settings');
 Route::post('/settings/reception',[SettingsController::class,'updateReception'])->name('settings.reception.update')->middleware('permission:edit_billing_settings');
 Route::resource('users',UserController::class)->middleware('permission:view_users');
 Route::post('/service-categories', [ServiceCategoryController::class, 'store'])
    ->name('service-categories.store');
});