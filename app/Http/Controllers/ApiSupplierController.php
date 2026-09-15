<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApiSupplierController extends Controller
{
    public function __construct(
        private readonly SupplierService $supplierService
    ) {}

    /**
     * Display a listing of suppliers.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Supplier::with(['contacts', 'addresses', 'bankAccounts', 'status'])
            ->when($request->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($request->status, fn ($q, $status) => $q->whereHas('status', fn ($q) => $q->where('key', $status)))
            ->when($request->is_blacklisted !== null, fn ($q, $blacklisted) => $q->where('is_blacklisted', $blacklisted))
            ->orderBy('name');

        $suppliers = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $suppliers,
        ]);
    }

    /**
     * Create a new supplier form.
     */
    public function create(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'payment_terms' => \App\Models\Setting::where('group', 'payment_terms')->get(),
                'address_types' => ['billing', 'shipping'],
            ],
        ]);
    }

    /**
     * Edit a supplier form.
     */
    public function edit(Supplier $supplier): JsonResponse
    {
        $supplier->load(['contacts', 'addresses', 'bankAccounts']);

        return response()->json([
            'success' => true,
            'data' => [
                'supplier' => $supplier,
                'payment_terms' => \App\Models\Setting::where('group', 'payment_terms')->get(),
                'address_types' => ['billing', 'shipping'],
            ],
        ]);
    }

    /**
     * Store a newly created supplier in storage.
     */
    public function store(StoreSupplierRequest $request): JsonResponse
    {
        try {
            $supplier = Supplier::create($request->validated());

            // Create contacts if provided
            if ($request->has('contacts')) {
                foreach ($request->contacts as $contact) {
                    $supplier->contacts()->create($contact);
                }
            }

            // Create addresses if provided
            if ($request->has('addresses')) {
                foreach ($request->addresses as $address) {
                    $supplier->addresses()->create($address);
                }
            }

            // Create bank accounts if provided
            if ($request->has('bank_accounts')) {
                foreach ($request->bank_accounts as $bankAccount) {
                    $supplier->bankAccounts()->create($bankAccount);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Supplier created successfully',
                'data' => $supplier->load(['contacts', 'addresses', 'bankAccounts']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create supplier: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified supplier.
     */
    public function show(Supplier $supplier): JsonResponse
    {
        $supplier->load(['contacts', 'addresses', 'bankAccounts', 'status', 'ledgers' => fn ($q) => $q->latest()->limit(10)]);

        return response()->json([
            'success' => true,
            'data' => $supplier,
        ]);
    }

    /**
     * Update the specified supplier in storage.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        try {
            $supplier->update($request->validated());

            // Update contacts if provided
            if ($request->has('contacts')) {
                $supplier->contacts()->delete();
                foreach ($request->contacts as $contact) {
                    $supplier->contacts()->create($contact);
                }
            }

            // Update addresses if provided
            if ($request->has('addresses')) {
                $supplier->addresses()->delete();
                foreach ($request->addresses as $address) {
                    $supplier->addresses()->create($address);
                }
            }

            // Update bank accounts if provided
            if ($request->has('bank_accounts')) {
                $supplier->bankAccounts()->delete();
                foreach ($request->bank_accounts as $bankAccount) {
                    $supplier->bankAccounts()->create($bankAccount);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Supplier updated successfully',
                'data' => $supplier->load(['contacts', 'addresses', 'bankAccounts']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update supplier: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified supplier from storage.
     */
    public function destroy(Supplier $supplier): JsonResponse
    {
        try {
            $supplier->delete();

            return response()->json([
                'success' => true,
                'message' => 'Supplier deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete supplier: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Blacklist a supplier.
     */
    public function blacklist(Request $request, Supplier $supplier): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:500']);

        try {
            $this->supplierService->blacklist($supplier, $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'Supplier blacklisted successfully',
                'data' => $supplier->refresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to blacklist supplier: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Unblacklist a supplier.
     */
    public function unblacklist(Supplier $supplier): JsonResponse
    {
        try {
            $this->supplierService->unblacklist($supplier);

            return response()->json([
                'success' => true,
                'message' => 'Supplier reinstated successfully',
                'data' => $supplier->refresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reinstate supplier: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get supplier ledger.
     */
    public function ledger(Supplier $supplier): JsonResponse
    {
        $ledger = $supplier->ledgers()
            ->with('entryType', 'createdBy')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $ledger,
        ]);
    }

    /**
     * Get supplier statistics.
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total' => Supplier::count(),
            'active' => Supplier::whereHas('status', fn ($q) => $q->where('key', 'active'))->count(),
            'blacklisted' => Supplier::where('is_blacklisted', true)->count(),
            'total_outstanding' => Supplier::sum('outstanding_balance'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
