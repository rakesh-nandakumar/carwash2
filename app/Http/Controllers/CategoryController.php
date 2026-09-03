<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('business_id', auth()->user()->business_id)
            ->whereNull('parent_id')
            ->with('children')
            ->latest()
            ->paginate(20);
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $categories = Category::where('business_id', auth()->user()->business_id)
            ->whereNull('parent_id')
            ->get();
        $selectedParent = request('parent_id');
        return view('categories.create', compact('categories', 'selectedParent'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        $validated['business_id'] = auth()->user()->business_id;
        Category::create($validated);

        return redirect()->route('categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category)
    {
        $categories = Category::where('business_id', auth()->user()->business_id)
            ->whereNull('parent_id')
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
