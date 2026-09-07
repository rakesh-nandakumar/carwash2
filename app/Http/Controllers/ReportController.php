<?php

namespace App\Http\Controllers;

use App\Models\{Invoice, Job, Customer, Vehicle, Product, Service, InventoryMovement, CashMovement};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        abort_unless(
            auth()->user()->hasPermissionTo('reports.access'),
            403
        );

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
        abort_unless(
            auth()->user()->hasPermissionTo('reports.sales'),
            403
        );

        $startDate = Carbon::parse(
            $request->input(
                'start_date',
                now()->startOfMonth()->toDateString()
            )
        )->startOfDay();

        $endDate = Carbon::parse(
            $request->input(
                'end_date',
                now()->toDateString()
            )
        )->endOfDay();

        $sales = Invoice::whereBetween('created_at', [
                $startDate,
                $endDate,
            ])
            ->with('customer', 'job.vehicle')
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        /*
         * Calculate totals from the complete filtered dataset,
         * not only the current pagination page.
         */
        $totals = Invoice::whereBetween('created_at', [
                $startDate,
                $endDate,
            ])
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
        abort_unless(
            auth()->user()->hasPermissionTo('reports.stock'),
            403
        );

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
        abort_unless(
            auth()->user()->hasPermissionTo('reports.stock_movement'),
            403
        );

        $startDate = Carbon::parse(
            $request->input(
                'start_date',
                now()->startOfMonth()->toDateString()
            )
        )->startOfDay();

        $endDate = Carbon::parse(
            $request->input(
                'end_date',
                now()->toDateString()
            )
        )->endOfDay();

        $user = auth()->user();

        /*
         * Base query
         *
         * Tenant isolation is already handled by
         * InventoryMovement's BelongsToTenant trait.
         */
        $query = InventoryMovement::query()
            ->with([
                'product',
                'branch',
                'user',
                'job',
            ])
            ->whereBetween('created_at', [
                $startDate,
                $endDate,
            ]);

        /*
         * Non-admin users can only see movements
         * belonging to their own branch.
         */
        if (!$user->isAdmin() && $user->branch_id) {
            $query->where(
                'branch_id',
                $user->branch_id
            );
        }

        /*
         * Get ALL movements for printing.
         *
         * Do not paginate this collection because:
         * - Thermal printing should print the whole report.
         * - PDF printing should print the whole report.
         */
        $printMovements = (clone $query)
            ->latest('created_at')
            ->get();

        /*
         * Screen version remains paginated.
         */
        $movements = $query
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view(
            'reports.stock_movement',
            compact(
                'movements',
                'printMovements',
                'startDate',
                'endDate'
            )
        );
    }

    public function serviceReport(Request $request)
    {
        abort_unless(
            auth()->user()->hasPermissionTo('reports.services'),
            403
        );

        $startDate = Carbon::parse(
            $request->input(
                'start_date',
                now()->startOfMonth()->toDateString()
            )
        )->startOfDay();

        $endDate = Carbon::parse(
            $request->input(
                'end_date',
                now()->toDateString()
            )
        )->endOfDay();

        $services = Job::whereBetween('created_at', [
                $startDate,
                $endDate,
            ])
            ->with('jobServices.service')
            ->get();

        $serviceStats = [];

        foreach ($services as $job) {
            foreach ($job->jobServices as $jobService) {

                $serviceName = $jobService->service->name ?? 'Unknown';

                if (!isset($serviceStats[$serviceName])) {
                    $serviceStats[$serviceName] = [
                        'count' => 0,
                        'revenue' => 0,
                    ];
                }

                $serviceStats[$serviceName]['count']++;

                $serviceStats[$serviceName]['revenue'] +=
                    (float) $jobService->unit_price *
                    (float) $jobService->quantity;
            }
        }

        return view(
            'reports.services',
            compact(
                'serviceStats',
                'startDate',
                'endDate'
            )
        );
    }

    public function customerReport(Request $request)
    {
        abort_unless(
            auth()->user()->hasPermissionTo('reports.customers'),
            403
        );

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfDay()->format('Y-m-d'));

        $customers = Customer::with([
            'jobs' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay(),
                ])->with('invoice');
            }
        ])
            ->paginate(20)
            ->withQueryString();

        return view('reports.customers', compact('customers', 'startDate', 'endDate'));
    }

    public function cashMovementsReport(Request $request)
    {
        abort_unless(
            auth()->user()->hasPermissionTo('cash_movements.access'),
            403
        );

        $startDate = Carbon::parse(
            $request->input(
                'start_date',
                now()->startOfMonth()->toDateString()
            )
        )->startOfDay();

        $endDate = Carbon::parse(
            $request->input(
                'end_date',
                now()->toDateString()
            )
        )->endOfDay();

        // Get cash movements
        $movementsQuery = CashMovement::query()
            ->with([
                'till',
                'user',
                'reference',
            ])
            ->whereBetween('created_at', [
                $startDate,
                $endDate,
            ]);

        if ($request->filled('type')) {
            $movementsQuery->where('type', $request->type);
        }

        if ($request->filled('source')) {
            $movementsQuery->where('source', $request->source);
        }

        if ($request->filled('user_id')) {
            $movementsQuery->where('user_id', $request->user_id);
        }

        if ($request->filled('reason')) {
            $movementsQuery->where('reason', 'like', '%' . $request->reason . '%');
        }

        // Get till closures within the date range
        $closuresQuery = \App\Models\TillClosure::query()
            ->with(['till', 'user'])
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('opened_at', [$startDate, $endDate])
                      ->orWhereBetween('closed_at', [$startDate, $endDate])
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->where('opened_at', '<=', $startDate)
                            ->where('closed_at', '>=', $endDate);
                      });
            });

        // Get collections
        $movements = $movementsQuery->latest('created_at')->get();
        $closures = $closuresQuery->latest('opened_at')->get();

        // Combine movements and closures into a single collection
        $combined = collect();

        // Add cash movements
        foreach ($movements as $movement) {
            $combined->push([
                'type' => 'movement',
                'data' => $movement,
                'date' => $movement->created_at,
            ]);
        }

        // Add closure events (both opening and closing)
        foreach ($closures as $closure) {
            // Opening event
            if ($closure->opened_at->between($startDate, $endDate)) {
                $combined->push([
                    'type' => 'closure_open',
                    'data' => $closure,
                    'date' => $closure->opened_at,
                ]);
            }

            // Closing event
            if ($closure->closed_at && $closure->closed_at->between($startDate, $endDate)) {
                $combined->push([
                    'type' => 'closure_close',
                    'data' => $closure,
                    'date' => $closure->closed_at,
                ]);
            }
        }

        // Sort by date descending
        $combined = $combined->sortByDesc('date')->values();

        // Calculate totals (only from cash movements)
        $cashIn = (float) $movements->where('type', 'in')->sum('amount');
        $cashOut = (float) $movements->where('type', 'out')->sum('amount');
        $sales = (float) $movements->where('type', 'in')->where('source', 'sale')->sum('amount');
        $refunds = (float) $movements->where('type', 'out')->where('source', 'refund')->sum('amount');
        $manualIn = (float) $movements->where('type', 'in')->where('source', 'manual')->sum('amount');
        $manualOut = (float) $movements->where('type', 'out')->where('source', 'manual')->sum('amount');
        $drops = (float) $movements->where('source', 'drop')->sum('amount');

        // Paginate the combined results
        $perPage = 30;
        $page = request()->get('page', 1);
        $paginatedCombined = new \Illuminate\Pagination\LengthAwarePaginator(
            $combined->forPage($page, $perPage),
            $combined->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $till = app(\App\Services\CashMovementService::class)
            ->mainTill();

        return view(
            'reports.cash_movements',
            compact(
                'movements',
                'closures',
                'combined',
                'paginatedCombined',
                'startDate',
                'endDate',
                'till',
                'cashIn',
                'cashOut',
                'sales',
                'refunds',
                'manualIn',
                'manualOut',
                'drops',
            )
        );
    }
}