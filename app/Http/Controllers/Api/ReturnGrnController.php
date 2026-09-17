<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReturnGrn;
use App\Services\ReturnGrnService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReturnGrnController extends Controller
{
    public function __construct(
        private readonly ReturnGrnService $returnGrnService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = ReturnGrn::with(['supplier', 'items.product', 'status'])
            ->when($request->supplier_id, fn ($q, $id) => $q->where('supplier_id', $id))
            ->when($request->status, fn ($q, $status) => $q->whereHas('status', fn ($q) => $q->where('key', $status)))
            ->when($request->search, fn ($q, $search) => $q->where('return_grn_number', 'like', "%{$search}%"))
            ->orderBy('created_at', 'desc');

        $returnGrns = $query->paginate($request->per_page ?? 15);

        // Add computed fields to each return GRN
        $returnGrns->getCollection()->transform(function ($returnGrn) {
            $returnGrn->items_count = $returnGrn->items->count();
            $totalAmount = 0;
            foreach ($returnGrn->items as $item) {
                if ($item->unit_cost) {
                    $totalAmount += $item->unit_cost * $item->quantity;
                }
            }
            $returnGrn->total_amount = $totalAmount;

            // Extract status as string
            $statusString = 'Draft';
            $statusKey = 'draft';
            
            if ($returnGrn->status) {
                $statusString = $returnGrn->status->value ?? 'Draft';
                $statusKey = $returnGrn->status->key ?? 'draft';
            }

            // Convert to array to remove the status object
            $returnGrnArray = $returnGrn->toArray();
            unset($returnGrnArray['status']);
            $returnGrnArray['status'] = $statusString;
            $returnGrnArray['status_key'] = $statusKey;

            return (object) $returnGrnArray;
        });

        return response()->json([
            'success' => true,
            'data' => $returnGrns,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $returnGrn = $this->returnGrnService->create(
                header: $request->only(['supplier_id', 'reference', 'address', 'reason', 'notes']),
                lines: $request->json('items')
            );

            return response()->json([
                'success' => true,
                'message' => 'Return GRN created successfully',
                'data' => $returnGrn->load(['supplier', 'items.product']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Return GRN: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(ReturnGrn $returnGrn): JsonResponse
    {
        $returnGrn->load(['supplier', 'items.product', 'status', 'returnedBy', 'deletedBy']);

        return response()->json([
            'success' => true,
            'data' => $returnGrn,
        ]);
    }

    public function confirm(ReturnGrn $returnGrn): JsonResponse
    {
        try {
            $confirmedReturnGrn = $this->returnGrnService->confirm($returnGrn, auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Return GRN confirmed successfully. Stock has been adjusted.',
                'data' => $confirmedReturnGrn->load(['supplier', 'items.product']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm Return GRN: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(ReturnGrn $returnGrn): JsonResponse
    {
        if ($returnGrn->isDeleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Return GRN is already deleted',
            ], 422);
        }

        try {
            $this->returnGrnService->delete($returnGrn, auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Return GRN deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Return GRN: ' . $e->getMessage(),
            ], 422);
        }
    }
}
