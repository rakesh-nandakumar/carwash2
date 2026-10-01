<?php namespace App\Http\Controllers; use App\Models\{Job,Customer,Vehicle,Invoice,Product}; use App\Services\ReportingService; use Illuminate\Support\Facades\DB;
class DashboardController extends Controller {public function __construct(private ReportingService $reporting){} public function index(){ $branch=auth()->user()->branch_id; $tenant=auth()->user()->tenant_id; $metrics=$this->reporting->getDashboardMetrics($branch, $tenant); $q=fn($m)=>$branch?$m::where('branch_id',$branch):$m::query();

    // Get vehicle revenue insights
    $vehicleRevenue = DB::table('vehicles')
        ->join('customers', 'vehicles.customer_id', '=', 'customers.id')
        ->join('jobs', 'vehicles.id', '=', 'jobs.vehicle_id')
        ->leftJoin('invoices', 'jobs.id', '=', 'invoices.job_id')
        ->select('vehicles.category', DB::raw('COUNT(DISTINCT jobs.id) as count'), DB::raw('COALESCE(SUM(invoices.total), 0) as total_revenue'))
        ->where('customers.tenant_id', $tenant)
        ->when($branch, fn($q) => $q->where('customers.branch_id', $branch))
        ->groupBy('vehicles.category')
        ->orderByDesc('total_revenue')
        ->limit(5)
        ->get();

    // Get frequent customers
    $frequentCustomers = DB::table('customers')
        ->join('vehicles', 'customers.id', '=', 'vehicles.customer_id')
        ->join('jobs', 'vehicles.id', '=', 'jobs.vehicle_id')
        ->select('customers.id', 'customers.full_name', DB::raw('COUNT(DISTINCT jobs.id) as job_count'), DB::raw('COALESCE(SUM(invoices.total), 0) as total_spent'))
        ->leftJoin('invoices', 'jobs.id', '=', 'invoices.job_id')
        ->where('customers.tenant_id', $tenant)
        ->when($branch, fn($q) => $q->where('customers.branch_id', $branch))
        ->groupBy('customers.id', 'customers.full_name')
        ->orderByDesc('job_count')
        ->limit(5)
        ->get();

    // Get service popularity
    $servicePopularity = DB::table('job_services')
        ->join('services', 'job_services.service_id', '=', 'services.id')
        ->where('services.tenant_id', $tenant)
        ->select('services.name', DB::raw('COUNT(*) as count'))
        ->groupBy('services.id', 'services.name')
        ->orderByDesc('count')
        ->limit(5)
        ->get();
    
    // Get low stock items
    $lowStockItems = Product::query()
        ->where('tenant_id', $tenant)
        ->with(['inventory' => function ($query) use ($branch) {
            $query->when($branch, fn($q) => $q->where('branch_id', $branch));
        }])
        ->whereHas('inventory', function ($query) use ($branch) {
            $query->when($branch, fn($q) => $q->where('branch_id', $branch));
        })
        ->get()
        ->filter(function ($product) {
            $inventory = $product->inventory->first();

            return $inventory &&
                $inventory->quantity <= $product->minimum_stock;
        })
        ->sortBy(function ($product) {
            return $product->inventory->first()->quantity;
        })
        ->take(10)
        ->values();

    // Calculate payment rate
    $totalInvoices = Invoice::where('tenant_id', $tenant)->count();
    $paidInvoices = Invoice::where('tenant_id', $tenant)->where('status', 'paid')->count();
    $paymentRate = $totalInvoices > 0 ? round(($paidInvoices / $totalInvoices) * 100, 1) : 0;

    return view('dashboard.index',[
        'vehiclesToday'=>Vehicle::whereHas('customer',fn($q)=>$q->where('tenant_id',$tenant)->when($branch,fn($q2)=>$q2->where('branch_id',$branch)))->whereDate('created_at',today())->count(),
        'activeJobs'=>$metrics['active_jobs'],
        'completedJobs'=>$metrics['this_month']['completed_jobs'],
        'revenue'=>$metrics['today']['revenue'],
        'pendingPayments'=>$metrics['pending_payments'],
        'lowStock'=>$metrics['low_stock'],
        'monthlyRevenue'=>$metrics['this_month']['revenue'],
        'monthlyJobs'=>$metrics['this_month']['jobs'],
        'vehicleRevenue'=>$vehicleRevenue,
        'frequentCustomers'=>$frequentCustomers,
        'servicePopularity'=>$servicePopularity,
        'lowStockItems'=>$lowStockItems,
        'paymentRate'=>$paymentRate
    ]);}}