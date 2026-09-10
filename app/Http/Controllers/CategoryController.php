<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::where('business_id', auth()->user()->business_id)
            ->whereNull('parent_id')
            ->with('children')
            ->latest()
            ->paginate(20);
        return view('categories.index', compact('categories'));
    }

    public function list()
    {
        $categories = Category::select('id', 'name')
            ->where('business_id', auth()->user()->business_id)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
        
        \Log::info('CategoryController::list - User business_id: ' . auth()->user()->business_id);
        \Log::info('CategoryController::list - Categories found: ' . $categories->count());
        
        return response()->json($categories);
    }

    public function create()
    {
        $selectedParent = request('parent_id');
        $selectedParentCategory = null;
        
        if ($selectedParent) {
            $selectedParentCategory = Category::where('business_id', auth()->user()->business_id)
                ->find($selectedParent);
        }
        
        return view('categories.create', compact('selectedParent', 'selectedParentCategory'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'parent_id' => 'nullable|integer|exists:categories,id'
        ]);

        $validated['business_id'] = auth()->user()->business_id;
        Category::create($validated);

        return redirect()->route('categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category)
    {
        $categories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->get();
        return view('categories.edit', compact('category', 'categories'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        $category->update($validated);
        return redirect()->route('categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Category deleted.');
    }
}
