<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::where('business_id', auth()->user()->business_id)
            ->latest()
            ->paginate(20);
        return view('categories.index', compact('categories'));
    }

    public function list()
    {
        $categories = Category::select('id', 'name')
            ->where('business_id', auth()->user()->business_id)
            ->orderBy('name')
            ->get();

        \Log::info('CategoryController::list - User business_id: ' . auth()->user()->business_id);
        \Log::info('CategoryController::list - Categories found: ' . $categories->count());

        return response()->json($categories);
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required'
        ]);

        $validated['business_id'] = auth()->user()->business_id;
        Category::create($validated);

        return redirect()->route('categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required'
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
