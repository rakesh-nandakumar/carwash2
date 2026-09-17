<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGrnRequest;
use App\Http\Requests\UpdateGrnRequest;
use App\Models\GoodsReceipt;
use App\Services\GrnService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GrnController extends Controller
{
    public function __construct(
        private readonly GrnService $grnService
    ) {}

    /**
     * Display a listing of GRNs.
     */
    public function index(Request $request): JsonResponse
    {
        $query = GoodsReceipt::with(['supplier', 'items.product', 'status'])
            ->when($request->supplier_id, fn ($q, $id) => $q->where('supplier_id', $id))
            ->when($request->status, fn ($q, $status) => $q->whereHas('status', fn ($q) => $q->where('key', $status)))
            ->when($request->search, fn ($q, $search) => $q->where('grn_number', 'like', "%{$search}%"))
            ->orderBy('created_at', 'desc');

        $grns = $query->paginate($request->per_page ?? 15);

        // Add computed fields to each GRN
        $grns->getCollection()->transform(function ($grn) {
            $grn->items_count = $grn->items->count();
            $totalAmount = 0;
            foreach ($grn->items as $item) {
                if ($item->unit_cost) {
                    $totalAmount += $item->unit_cost * $item->quantity;
                }
            }
            $grn->total_amount = $totalAmount;

            // Extract status as string
            $statusString = 'Draft';
            $statusKey = 'draft';
            
            if ($grn->status) {
                $statusString = $grn->status->value ?? 'Draft';
                $statusKey = $grn->status->key ?? 'draft';
            }

            // Convert to array to remove the status object
            $grnArray = $grn->toArray();
            unset($grnArray['status']);
            $grnArray['status'] = $statusString;
            $grnArray['status_key'] = $statusKey;

            return (object) $grnArray;
        });

        return response()->json([
            'success' => true,
            'data' => $grns,
        ]);
    }

    /**
     * Store a newly created GRN in storage.
     */
    public function store(StoreGrnRequest $request): JsonResponse
    {
        try {
            // Log the incoming request
            \Log::info('GRN Store Request', [
                'header' => $request->only(['supplier_id', 'purchase_order_id', 'reference', 'note']),
                'items' => $request->items,
            ]);

            $grn = $this->grnService->create(
                header: $request->only(['supplier_id', 'purchase_order_id', 'reference', 'note']),
                lines: $request->items
            );

            return response()->json([
                'success' => true,
                'message' => 'GRN created successfully',
                'data' => $grn->load(['supplier', 'items.product']),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('GRN Validation Error', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('GRN Creation Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create GRN: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /**
     * Display the specified GRN.
     */
    public function show(GoodsReceipt $grn): JsonResponse
    {
        $grn->load(['supplier', 'items.product', 'status', 'confirmedBy', 'deletedBy']);

        return response()->json([
            'success' => true,
            'data' => $grn,
        ]);
    }

    /**
     * Update the specified GRN in storage (only draft GRNs can be updated).
     */
    public function update(UpdateGrnRequest $request, GoodsReceipt $grn): JsonResponse
    {
        if (!$grn->isDraft()) {
            return response()->json([
                'success' => false,
                'message' => 'Only draft GRNs can be updated',
            ], 422);
        }

        try {
            $grn->update($request->only(['reference', 'note']));

            // Update items if provided
            if ($request->has('items')) {
                foreach ($request->items as $itemData) {
                    $grn->items()->updateOrCreate(
                        ['product_id' => $itemData['product_id']],
                        [
                            'quantity' => $itemData['quantity'],
                            'unit_cost' => $itemData['unit_cost'] ?? null,
                            'sale_price' => $itemData['sale_price'] ?? null,
                            'notes' => $itemData['notes'] ?? null,
                        ]
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'GRN updated successfully',
                'data' => $grn->load(['supplier', 'items.product']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update GRN: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Confirm a draft GRN.
     */
    public function confirm(GoodsReceipt $grn): JsonResponse
    {
        $confirmedGrn = $this->grnService->confirm($grn, auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'GRN confirmed successfully. Stock has been updated.',
            'data' => $confirmedGrn->load(['supplier', 'items.product']),
        ]);
    }

    /**
     * Delete a GRN.
     */
    public function destroy(GoodsReceipt $grn): JsonResponse
    {
        if ($grn->isDeleted()) {
            return response()->json([
                'success' => false,
                'message' => 'GRN is already deleted',
            ], 422);
        }

        try {
            $this->grnService->delete($grn, auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'GRN deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete GRN: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Revert a confirmed GRN back to draft.
     */
    public function revert(GoodsReceipt $grn): JsonResponse
    {
        if (!$grn->isConfirmed()) {
            return response()->json([
                'success' => false,
                'message' => 'Only confirmed GRNs can be reverted to draft',
            ], 422);
        }

        try {
            $this->grnService->revert($grn, auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'GRN reverted to draft successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to revert GRN: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get GRN statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = GoodsReceipt::query();

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        $stats = [
            'total' => $query->count(),
            'draft' => (clone $query)->whereHas('status', fn ($q) => $q->where('key', 'draft'))->count(),
            'confirmed' => (clone $query)->whereHas('status', fn ($q) => $q->where('key', 'confirmed'))->count(),
            'deleted' => (clone $query)->whereHas('status', fn ($q) => $q->where('key', 'deleted'))->count(),
            'this_month' => (clone $query)->whereMonth('created_at', now()->month)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
