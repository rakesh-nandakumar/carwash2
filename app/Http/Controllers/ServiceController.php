<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->get('search');

        $query = Service::with('category')
            ->where('tenant_id', $user->tenant_id)
            ->orderBy('name');

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $services = $query->get();
        return view('services.index', compact('services', 'search'));
    }

    public function create()
    {
        return view('services.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'nullable|numeric|min:0',
            'labor_cost' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'service_category_id' => 'nullable|integer|exists:service_categories,id',
            'duration_minutes' => 'nullable|integer|min:1',
            'active' => 'nullable|boolean',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['business_id'] = auth()->user()->business_id;

        $validated['active'] = $request->boolean('active');

        $validated['labor_cost'] = $validated['labor_cost'] ?? 0;
        $validated['base_price'] = $validated['base_price'] ?? 0;
        $validated['tax_rate'] = $validated['tax_rate'] ?? 0;
        $validated['duration_minutes'] = $validated['duration_minutes'] ?? 30;

        Service::create($validated);

        return redirect()->route('services.index')->with('success', 'Service created successfully.');
    }

    public function show(Service $service)
    {
        $service->load('category');
        return view('services.show', compact('service'));
    }

    public function edit(Service $service)
    {
        $categories = ServiceCategory::orderBy('name')->get();
        return view('services.edit', compact('service', 'categories'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'nullable|numeric|min:0',
            'labor_cost' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'service_category_id' => 'nullable|integer|exists:service_categories,id',
            'duration_minutes' => 'nullable|integer|min:1',
            'active' => 'nullable|boolean',
        ]);

        $validated['active'] = $request->boolean('active');

        $validated['labor_cost'] = $validated['labor_cost'] ?? 0;
        $validated['base_price'] = $validated['base_price'] ?? 0;
        $validated['tax_rate'] = $validated['tax_rate'] ?? 0;
        $validated['duration_minutes'] = $validated['duration_minutes'] ?? 30;

        $service->update($validated);

        return redirect()->route('services.index')->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        // Check if service is used in any jobs
        if ($service->jobServices()->exists()) {
            return redirect()->route('services.index')
                ->with('error', 'Cannot delete service that is used in jobs. Please deactivate it instead.');
        }

        $service->delete();
        return redirect()->route('services.index')->with('success', 'Service deleted successfully.');
    }

    public function toggle(Request $request, Service $service)
    {
        $service->update(['active' => $request->boolean('active')]);
        return response()->json(['success' => true, 'active' => $service->active]);
    }
}