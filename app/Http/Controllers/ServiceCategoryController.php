<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function list(Request $request)
    {
        $query = ServiceCategory::select('id', 'name', 'vehicle_category')
            ->where('business_id', auth()->user()->business_id);

        // Filter by vehicle category if provided
        if ($request->has('vehicle_category') && $request->vehicle_category) {
            $query->where('vehicle_category', $request->vehicle_category);
        }

        $categories = $query->orderBy('name')->get();

        \Log::info('ServiceCategoryController::list - User business_id: ' . auth()->user()->business_id);
        \Log::info('ServiceCategoryController::list - Vehicle category filter: ' . ($request->vehicle_category ?? 'none'));
        \Log::info('ServiceCategoryController::list - Categories found: ' . $categories->count());

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:service_categories,name,NULL,id,business_id,' . auth()->user()->business_id,
                'vehicle_category' => 'nullable|string',
            ]);

            $validated['business_id'] = auth()->user()->business_id;

            $category = ServiceCategory::create($validated);

            return response()->json([
                'success' => true,
                'category' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}