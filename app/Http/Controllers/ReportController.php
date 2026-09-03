<?php

namespace App\Http\Controllers;

use App\Models\{Invoice, Job, Customer, Vehicle, Product, Service};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index', [
            'revenue' => Invoice::sum('total'),
            'paid' => Invoice::sum('paid'),
            'outstanding' => Invoice::sum('balance'),
            'jobs' => Job::count(),
            'customers' => Customer::count(),
            'vehicles' => Vehicle::count(),
            'stockValue' => DB::table('inventory')
                ->join('products', 'products.id', '=', 'inventory.product_id')
                ->selectRaw('COALESCE(SUM(inventory.quantity * products.cost_price), 0) v')
                ->value('v')
        ]);
    }

    public function salesReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfDay()->format('Y-m-d'));

        $sales = Invoice::whereBetween('created_at', [$startDate, $endDate])
            ->with('customer', 'job.vehicle')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Totals should be calculated on the full filtered set (not just current page)
        $totals = Invoice::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                COALESCE(SUM(total), 0) as total_revenue,
                COALESCE(SUM(paid), 0) as total_paid,
                COALESCE(SUM(balance), 0) as total_outstanding
            ')
            ->first();

        $totalRevenue = $totals->total_revenue;
        $totalPaid = $totals->total_paid;
        $totalOutstanding = $totals->total_outstanding;

        return view('reports.sales', compact(
            'sales',
            'startDate',
            'endDate',
            'totalRevenue',
            'totalPaid',
            'totalOutstanding'
        ));
    }

    public function stockReport(Request $request)
    {
        // Paginated list
        $stock = DB::table('inventory')
            ->join('products', 'products.id', '=', 'inventory.product_id')
            ->select(
                'products.name',
                'products.sku',
                'products.cost_price',
                'products.selling_price',
                'inventory.quantity',
                DB::raw('inventory.quantity * products.cost_price as total_cost'),
                DB::raw('inventory.quantity * products.selling_price as total_value')
            )
            ->orderBy('products.name')
            ->paginate(20)
            ->withQueryString();

        // Totals on the full dataset
        $totals = DB::table('inventory')
            ->join('products', 'products.id', '=', 'inventory.product_id')
            ->selectRaw('
                COALESCE(SUM(inventory.quantity * products.cost_price), 0) as total_stock_value,
                COALESCE(SUM(inventory.quantity * products.selling_price), 0) as total_retail_value
            ')
            ->first();

        $totalStockValue = $totals->total_stock_value;
        $totalRetailValue = $totals->total_retail_value;

        return view('reports.stock', compact('stock', 'totalStockValue', 'totalRetailValue'));
    }

    public function stockMovementReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfDay()->format('Y-m-d'));

        $movements = DB::table('inventory_movements')
            ->join('products', 'products.id', '=', 'inventory_movements.product_id')
            ->whereBetween('inventory_movements.created_at', [$startDate, $endDate])
            ->select(
                'inventory_movements.*',
                'products.name',
                'products.sku'
            )
            ->orderBy('inventory_movements.created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('reports.stock_movement', compact('movements', 'startDate', 'endDate'));
    }

    public function serviceReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfDay()->format('Y-m-d'));

        // This one is aggregated, so we keep it as collection for now
        // (you can paginate later if needed)
        $services = Job::whereBetween('created_at', [$startDate, $endDate])
            ->with('jobServices.service')
            ->get();

        $serviceStats = [];
        foreach ($services as $job) {
            foreach ($job->jobServices as $jobService) {
                $serviceName = $jobService->service->name ?? 'Unknown';
                if (!isset($serviceStats[$serviceName])) {
                    $serviceStats[$serviceName] = [
                        'count' => 0,
                        'revenue' => 0
                    ];
                }
                $serviceStats[$serviceName]['count']++;
                $serviceStats[$serviceName]['revenue'] += $jobService->price;
            }
        }

        return view('reports.services', compact('serviceStats', 'startDate', 'endDate'));
    }

    public function customerReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfDay()->format('Y-m-d'));

        $customers = Customer::with(['jobs' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->whereHas('jobs', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->paginate(20)
            ->withQueryString();

        return view('reports.customers', compact('customers', 'startDate', 'endDate'));
    }
}