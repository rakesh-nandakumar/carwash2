<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SupplierController extends Controller
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
     * Display a listing of suppliers.
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
        $validated = $request->validated();

        $supplier = Supplier::create([
            'name' => $validated['name'],
            'business_name' => $validated['business_name'] ?? null,
            'company' => $validated['company'] ?? null,
            'registration_number' => $validated['registration_number'] ?? null,
            'tax_number' => $validated['tax_number'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'website' => $validated['website'] ?? null,
            'address' => $validated['address'] ?? null,
            'payment_terms' => $validated['payment_terms'] ?? null,
            'payment_terms_id' => $validated['payment_terms_id'] ?? null,
            'credit_limit' => $validated['credit_limit'] ?? 0,
            'notes' => $validated['notes'] ?? null,
            'business_id' => app(\App\Services\CurrentContext::class)->tenantId(),
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $supplier,
        ]);
    }

    /**
     * Display the specified supplier.
     */
    public function show(Supplier $supplier): JsonResponse
    {
        $supplier->load(['contacts', 'addresses', 'bankAccounts', 'ledgers', 'status']);

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
        $validated = $request->validated();

        $supplier->update([
            'name' => $validated['name'],
            'business_name' => $validated['business_name'] ?? null,
            'company' => $validated['company'] ?? null,
            'registration_number' => $validated['registration_number'] ?? null,
            'tax_number' => $validated['tax_number'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'website' => $validated['website'] ?? null,
            'address' => $validated['address'] ?? null,
            'payment_terms' => $validated['payment_terms'] ?? null,
            'payment_terms_id' => $validated['payment_terms_id'] ?? null,
            'credit_limit' => $validated['credit_limit'] ?? 0,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $supplier,
        ]);
    }

    /**
     * Remove the specified supplier from storage.
     */
    public function destroy(Supplier $supplier): JsonResponse
    {
        $supplier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Supplier deleted successfully',
        ]);
    }

    /**
     * Blacklist a supplier.
     */
    public function blacklist(Request $request, Supplier $supplier): JsonResponse
    {
        $reason = $request->input('reason', 'Blacklisted by administrator');

        $this->supplierService->blacklist($supplier, $reason);

        return response()->json([
            'success' => true,
            'message' => 'Supplier blacklisted successfully',
        ]);
    }

    /**
     * Unblacklist a supplier.
     */
    public function unblacklist(Supplier $supplier): JsonResponse
    {
        $this->supplierService->unblacklist($supplier);

        return response()->json([
            'success' => true,
            'message' => 'Supplier unblacklisted successfully',
        ]);
    }

    /**
     * Get supplier ledger.
     */
    public function ledger(Supplier $supplier): JsonResponse
    {
        $ledgers = $supplier->ledgers()->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $ledgers,
        ]);
    }

    /**
     * Get supplier statistics.
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total' => Supplier::count(),
            'active' => Supplier::where('is_blacklisted', false)->count(),
            'blacklisted' => Supplier::where('is_blacklisted', true)->count(),
            'total_balance' => Supplier::sum('outstanding_balance'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
