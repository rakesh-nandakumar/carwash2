<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function list()
    {
        $categories = ServiceCategory::select('id', 'name')
            ->where('business_id', auth()->user()->business_id)
            ->orderBy('name')
            ->get();
        
        \Log::info('ServiceCategoryController::list - User business_id: ' . auth()->user()->business_id);
        \Log::info('ServiceCategoryController::list - Categories found: ' . $categories->count());
        
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:service_categories,name,NULL,id,business_id,' . auth()->user()->business_id,
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