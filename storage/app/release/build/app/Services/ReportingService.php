<?php

namespace App\Services;

use App\Models\{Invoice,Job,Payment,Expense,Inventory,Product,Customer,User};
use Illuminate\Support\Facades\DB;

class ReportingService
{
    public function getSalesReport(string $startDate, string $endDate, ?int $branchId = null): array
    {
        $query = Invoice::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $invoices = $query->get();

        return [
            'total_revenue' => $invoices->sum('total'),
            'total_paid' => $invoices->sum('paid'),
            'total_balance' => $invoices->sum('balance'),
            'invoice_count' => $invoices->count(),
            'paid_invoices' => $invoices->where('status', 'paid')->count(),
            'pending_invoices' => $invoices->where('status', '!=', 'paid')->count(),
            'average_invoice_value' => $invoices->count() > 0 ? $invoices->avg('total') : 0,
            'daily_breakdown' => $invoices->groupBy(function ($invoice) {
                return $invoice->created_at->format('Y-m-d');
            })->map(function ($day) {
                return [
                    'count' => $day->count(),
                    'total' => $day->sum('total'),
                ];
            }),
        ];
    }

    public function getServiceReport(string $startDate, string $endDate, ?int $branchId = null): array
    {
        $query = Job::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $jobs = $query->with(['services', 'parts'])->get();

        return [
            'total_jobs' => $jobs->count(),
            'completed_jobs' => $jobs->where('status', 'delivered')->count(),
            'in_progress' => $jobs->whereNotIn('status', ['delivered', 'cancelled'])->count(),
            'services_revenue' => $jobs->sum(function ($job) {
                return $job->services->sum('unit_price');
            }),
            'parts_revenue' => $jobs->sum(function ($job) {
                return $job->parts->sum(function ($part) {
                    return $part->quantity * $part->unit_price;
                });
            }),
            'average_service_time' => $jobs->where('checked_in_at')->where('completed_at')
                ->avg(function ($job) {
                    return $job->checked_in_at->diffInMinutes($job->completed_at);
                }),
            'service_breakdown' => $jobs->flatMap->services->groupBy('name_snapshot')->map(function ($services) {
                return [
                    'count' => $services->count(),
                    'revenue' => $services->sum('unit_price'),
                ];
            }),
        ];
    }

    public function getInventoryReport(?int $branchId = null): array
    {
        $query = Inventory::with('product');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $inventory = $query->get();

        $totalValue = $inventory->sum(function ($item) {
            return $item->quantity * $item->product->cost_price;
        });

        $lowStock = $inventory->filter(function ($item) {
            return $item->quantity <= $item->product->minimum_stock;
        });

        return [
            'total_products' => $inventory->count(),
            'total_value' => $totalValue,
            'low_stock_count' => $lowStock->count(),
            'out_of_stock' => $inventory->where('quantity', 0)->count(),
            'low_stock_items' => $lowStock->map(function ($item) {
                return [
                    'product' => $item->product->name,
                    'current' => $item->quantity,
                    'minimum' => $item->product->minimum_stock,
                ];
            }),
            'category_breakdown' => $inventory->groupBy('product.category')->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'value' => $items->sum(function ($item) {
                        return $item->quantity * $item->product->cost_price;
                    }),
                ];
            }),
        ];
    }

    public function getFinancialReport(string $startDate, string $endDate, ?int $branchId = null): array
    {
        $query = Invoice::whereBetween('created_at', [$startDate, $endDate]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $invoices = $query->get();

        $expenseQuery = Expense::whereBetween('expense_date', [$startDate, $endDate]);

        if ($branchId) {
            $expenseQuery->where('branch_id', $branchId);
        }

        $expenses = $expenseQuery->get();

        $revenue = $invoices->sum('total');
        $totalExpenses = $expenses->sum('amount');
        $profit = $revenue - $totalExpenses;

        return [
            'revenue' => $revenue,
            'expenses' => $totalExpenses,
            'profit' => $profit,
            'profit_margin' => $revenue > 0 ? ($profit / $revenue) * 100 : 0,
            'expense_breakdown' => $expenses->groupBy('category')->map(function ($items) {
                return [
                    'amount' => $items->sum('amount'),
                    'count' => $items->count(),
                ];
            }),
            'payment_methods' => Payment::whereBetween('created_at', [$startDate, $endDate])
                ->whereHas('invoice', function ($q) use ($branchId) {
                    if ($branchId) {
                        $q->where('branch_id', $branchId);
                    }
                })
                ->get()
                ->groupBy('method')
                ->map(function ($payments) {
                    return [
                        'count' => $payments->count(),
                        'amount' => $payments->sum('amount'),
                    ];
                }),
        ];
    }

    public function getCustomerReport(string $startDate, string $endDate, ?int $branchId = null): array
    {
        $query = Invoice::whereBetween('created_at', [$startDate, $endDate]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $invoices = $query->with('customer')->get();

        $topCustomers = $invoices->groupBy('customer_id')
            ->map(function ($customerInvoices) {
                return [
                    'customer' => $customerInvoices->first()->customer,
                    'total_spent' => $customerInvoices->sum('total'),
                    'visit_count' => $customerInvoices->count(),
                ];
            })
            ->sortByDesc('total_spent')
            ->take(10)
            ->values();

        return [
            'total_customers' => $invoices->pluck('customer_id')->unique()->count(),
            'new_customers' => Customer::whereBetween('created_at', [$startDate, $endDate])->count(),
            'average_spend_per_customer' => $invoices->count() > 0 ? $invoices->avg('total') : 0,
            'top_customers' => $topCustomers,
            'repeat_customers' => $invoices->groupBy('customer_id')
                ->filter(function ($customerInvoices) {
                    return $customerInvoices->count() > 1;
                })
                ->count(),
        ];
    }

    public function getEmployeeReport(string $startDate, string $endDate, ?int $branchId = null): array
    {
        $query = Job::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('technician_id');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $jobs = $query->with('technician')->get();

        $technicianPerformance = $jobs->groupBy('technician_id')
            ->map(function ($technicianJobs) {
                return [
                    'technician' => $technicianJobs->first()->technician,
                    'jobs_completed' => $technicianJobs->where('status', 'delivered')->count(),
                    'jobs_in_progress' => $technicianJobs->whereNotIn('status', ['delivered', 'cancelled'])->count(),
                    'total_revenue' => $technicianJobs->sum(function ($job) {
                        return $job->invoice?->total ?? 0;
                    }),
                ];
            })
            ->sortByDesc('jobs_completed');

        return [
            'total_technicians' => $technicianPerformance->count(),
            'technician_performance' => $technicianPerformance,
            'average_jobs_per_technician' => $technicianPerformance->count() > 0 
                ? $technicianPerformance->avg('jobs_completed') 
                : 0,
        ];
    }

    public function getDashboardMetrics(?int $branchId = null): array
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        $jobsQuery = Job::where('status', '!=', 'cancelled');
        $invoicesQuery = Invoice::where('status', '!=', 'cancelled');

        if ($branchId) {
            $jobsQuery->where('branch_id', $branchId);
            $invoicesQuery->where('branch_id', $branchId);
        }

        return [
            'today' => [
                'revenue' => (clone $invoicesQuery)->whereDate('created_at', $today)->sum('total'),
                'jobs' => (clone $jobsQuery)->whereDate('created_at', $today)->count(),
                'completed_jobs' => (clone $jobsQuery)->whereDate('created_at', $today)->where('status', 'delivered')->count(),
            ],
            'this_month' => [
                'revenue' => (clone $invoicesQuery)->where('created_at', '>=', $thisMonth)->sum('total'),
                'jobs' => (clone $jobsQuery)->where('created_at', '>=', $thisMonth)->count(),
                'completed_jobs' => (clone $jobsQuery)->where('created_at', '>=', $thisMonth)->where('status', 'delivered')->count(),
            ],
            'active_jobs' => (clone $jobsQuery)->whereNotIn('status', ['delivered', 'cancelled'])->count(),
            'pending_payments' => (clone $invoicesQuery)->where('balance', '>', 0)->sum('balance'),
            'low_stock' => Inventory::when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })->with('product')->get()->filter(function ($item) {
                return $item->quantity <= $item->product->minimum_stock;
            })->count(),
        ];
    }
}
