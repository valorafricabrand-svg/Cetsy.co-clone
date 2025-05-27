<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    
    /**
     * Public: Display a listing of top‐level categories.
     */
    public function index()
    {
        $categories = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    /**
     * Public: Redirect to products filtered by this category.
     */
    public function show(Category $category)
    {
        return redirect()->route('products.index', [
            'category_id' => $category->id,
        ]);
    }

    /**
     * Admin: Show the form for creating a new category.
     */
    public function create()
    {
        $parents = Category::orderBy('name')->get();
        return view('categories.create', compact('parents'));
    }

    /**
     * Admin: Store a newly created category in storage, with featured image.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255|unique:categories,name',
            'slug'      => 'nullable|string|max:255|unique:categories,slug',
            'parent_id' => 'nullable|exists:categories,id',
            'image'     => 'nullable|image|max:20480',
        ]);

        // Auto-generate slug if blank, ensure uniqueness
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
            if (Category::where('slug', $data['slug'])->exists()) {
                $data['slug'] .= '-' . time();
            }
        }

        // Handle featured image upload
        if ($file = $request->file('image')) {
            $data['image'] = $file->store('categories/images', 'public');
        }

        Category::create($data);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category created successfully!');
    }

    /**
     * Admin: Show the form for editing the specified category.
     */
    public function edit(Category $category)
    {
        $parents = Category::where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view('categories.edit', compact('category', 'parents'));
    }

    /**
     * Admin: Update the specified category in storage, including featured image.
     */
    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'      => "required|string|max:255|unique:categories,name,{$category->id}",
            'slug'      => "nullable|string|max:255|unique:categories,slug,{$category->id}",
            'parent_id' => 'nullable|exists:categories,id',
            'image'     => 'nullable|image|max:2048',
        ]);

        // Auto-generate slug if blank
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Replace featured image if a new one was uploaded
        if ($file = $request->file('image')) {
            // Remove old image
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $file->store('categories/images', 'public');
        }

        $category->update($data);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category updated successfully!');
    }

    /**
     * Admin: Remove the specified category from storage along with its image.
     */
    public function destroy(Category $category)
    {
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category deleted successfully!');
    }


public function categoryShow($slug)
{
    $category = Category::where('slug', $slug)->firstOrFail();
    $products = $category->products()->latest()->get(); // Adjust as needed
    return view('theme.show_category', compact('category', 'products'));
}


}
