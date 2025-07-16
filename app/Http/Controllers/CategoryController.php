<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class CategoryController extends Controller
{
    


public function index(Request $request)
{
    // Grab the query string, if any (e.g. /admin/categories?q=shoes)
    $search = trim($request->input('q'));

    $parents = Category::with([
            'children' => function ($q) use ($search) {
                // Always order children alphabetically …
                $q->orderBy('name');

                // … and, when searching, keep only the children that match.
                if ($search !== '') {
                    $q->where('name', 'like', "%{$search}%");
                }
            }
        ])
        ->whereNull('parent_id')
        ->when($search !== '', function ($q) use ($search) {
            /* Show a top-level (parent) row if:
               1) the parent’s own name matches   OR
               2) at least one of its children matches. */
            $q->where('name', 'like', "%{$search}%")
               ->orWhereHas('children', function ($q2) use ($search) {
                   $q2->where('name', 'like', "%{$search}%");
               });
        })
        ->orderBy('name')
        ->get();

    return view('categories.index', compact('parents'));
}



    /**
     * Public: Redirect to products filtered by this category.
     */
public function show(Category $category)
{
    return view('categories.show', compact('category'));
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
        'name'          => 'required|string|max:255|unique:categories,name',
        'slug'          => 'nullable|string|max:255|unique:categories,slug',
        'parent_id'     => 'nullable|exists:categories,id',
        'listing_type'  => 'required|in:products,services,digital',
        'description'   => 'nullable|string|max:1000',
        'listing_fee'   => 'nullable|numeric|min:0',
        'image'         => 'nullable|image|max:20480',
    ]);

    // Auto‐slug if blank
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
        ->route('admin.categories.index')
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
        'name'          => "required|string|max:255|unique:categories,name,{$category->id}",
        'slug'          => "nullable|string|max:255|unique:categories,slug,{$category->id}",
        'parent_id'     => 'nullable|exists:categories,id',
        'listing_type'  => 'required|in:products,services,digital',
        'description'   => 'nullable|string|max:1000',
        'listing_fee'   => 'nullable|numeric|min:0',
        'image'         => 'nullable|image|max:20480',
    ]);

    // Auto‐slug if blank
    if (empty($data['slug'])) {
        $data['slug'] = Str::slug($data['name']);
    }

    // Replace featured image if uploaded
    if ($file = $request->file('image')) {
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
        $data['image'] = $file->store('categories/images', 'public');
    }

    $category->update($data);

    return back()->with('success', 'Category updated successfully!');
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
       // Find the category or 404
        $category = Category::where('slug', $slug)->firstOrFail();

        // Paginate products 12 per page, newest first
        $products = $category
            ->products()
            ->latest()
            ->paginate(12)
            ->withQueryString();
    return themed_view('show_category', compact('category', 'products'));
}



// app/Http/Controllers/CategoryController.php
public function attributeTemplate($id)
{

$category = Category::find($id);
    return response()->json(
        $category->attributes()
                 ->with('values:id,category_attribute_id,value')
                 ->get(['id','name'])
    );
}


// app/Http/Controllers/CategoryController.php

public function byType(string $type)
{
    // Map your form types to listing_type values in the database
    $map = [
      'physical' => 'products',
      'service'  => 'services',
      'digital'  => 'digital downloads',
    ];
    $listingType = $map[$type] ?? null;
    if (! $listingType) {
        return response()->json([], 400);
    }

    // Fetch parents and their children
    $categories = Category::where('listing_type', $listingType)
                       ->whereNotNull('parent_id')
                       ->orderBy('name')
                       ->get(['id','name']);

    return response()->json($categories);
}




}
