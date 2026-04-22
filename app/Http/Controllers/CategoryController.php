<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class CategoryController extends Controller
{
    


public function publicIndex(Request $request)
{
    $search = trim((string) $request->input('q', ''));
    $type = (string) $request->input('type', '');
    $allowedTypes = ['products', 'services', 'digital'];

    if (!in_array($type, $allowedTypes, true)) {
        $type = '';
    }

    $childrenQuery = function ($query) use ($type) {
        $query->withCount([
                'products as active_products_count' => fn ($productQuery) => $productQuery->where('is_active', 1),
            ])
            ->when($type !== '', fn ($categoryQuery) => $categoryQuery->where('listing_type', $type))
            ->orderBy('name');
    };

    $categories = Category::query()
        ->whereNull('parent_id')
        ->with(['children' => $childrenQuery])
        ->withCount([
            'products as active_products_count' => fn ($query) => $query->where('is_active', 1),
        ])
        ->when($search !== '', function ($query) use ($search) {
            $query->where(function ($nested) use ($search) {
                $nested->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('children', function ($childQuery) use ($search) {
                        $childQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
            });
        })
        ->when($type !== '', function ($query) use ($type) {
            $query->where(function ($nested) use ($type) {
                $nested->where('listing_type', $type)
                    ->orWhereHas('children', fn ($childQuery) => $childQuery->where('listing_type', $type));
            });
        })
        ->orderBy('name')
        ->get();

    $featuredProducts = \App\Models\Product::query()
        ->where('is_active', 1)
        ->with(['media', 'category:id,name,slug', 'shop:id,name,slug'])
        ->latest()
        ->take(8)
        ->get();

    return themed_view('categories', compact('categories', 'featuredProducts', 'search', 'type'));
}


public function index(Request $request)
{
    $search = trim($request->input('q'));

    // Re-usable filter for children collections (keeps a child if it or any of its children match)
    $childrenFilter = function ($q) use ($search) {
        $q->orderBy('name');

        if ($search !== '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhereHas('children', function ($q2) use ($search) {
                       $q2->where('name', 'like', "%{$search}%")
                          // if you want to go one more level deep, keep this nested whereHas:
                          ->orWhereHas('children', function ($q3) use ($search) {
                              $q3->where('name', 'like', "%{$search}%");
                          });
                   });
            });
        }
    };

    $parents = Category::with([
            // Eager-load children and grandchildren
            'children' => $childrenFilter,
            'children.children' => $childrenFilter,
        ])
        ->whereNull('parent_id')
        ->when($search !== '', function ($q) use ($search) {
            // Show a parent if it matches, OR one of its children matches, OR one of its grandchildren matches
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhereHas('children', function ($q2) use ($search) {
                       $q2->where('name', 'like', "%{$search}%");
                   })
                   ->orWhereHas('children.children', function ($q3) use ($search) {
                       $q3->where('name', 'like', "%{$search}%");
                   });
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
        'listing_frequency' => 'required|in:1,4',
        'image'         => 'nullable|image|max:20480',
    ]);

    // If listing_fee was left blank, drop it so DB default applies
    if (array_key_exists('listing_fee', $data) && ($data['listing_fee'] === null || $data['listing_fee'] === '')) {
        unset($data['listing_fee']);
    }

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
        'listing_frequency' => 'required|in:1,4',
        'image'         => 'nullable|image|max:20480',
    ]);

    // Preserve existing listing_fee if left blank
    if (array_key_exists('listing_fee', $data) && ($data['listing_fee'] === null || $data['listing_fee'] === '')) {
        unset($data['listing_fee']);
    }

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
            ->route('admin.categories.index')
            ->with('success', 'Category deleted successfully!');
    }


public function categoryShow($slug)
{
    // Find the category (with descendants) or 404
    $category = Category::with('childrenRecursive')
        ->where('slug', $slug)
        ->firstOrFail();

    // Build a flat list of this category + all descendant IDs
    $ids = collect([$category->id]);
    $stack = $category->childrenRecursive ?? collect();
    $stack = $stack instanceof \Illuminate\Support\Collection ? $stack : collect($stack);
    while ($stack->isNotEmpty()) {
        $node = $stack->shift();
        $ids->push($node->id);
        if (!empty($node->childrenRecursive)) {
            $stack = $stack->concat(
                $node->childrenRecursive instanceof \Illuminate\Support\Collection
                    ? $node->childrenRecursive
                    : collect($node->childrenRecursive)
            );
        }
    }

    $productQuery = \App\Models\Product::whereIn('category_id', $ids->unique()->values())
        ->where('is_active', 1)
        ->with([
            'media',
            'variations',
            'shop' => function ($q) {
                $q->withCount('reviews')->withAvg('reviews', 'rating');
            },
        ]);

    // Search query (name/description/tags)
    $q = trim((string) request('q', ''));
    if ($q !== '') {
        $terms = collect(preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->values();

        $productQuery->where(function ($qq) use ($terms, $q) {
            if ($terms->isEmpty()) {
                $like = "%{$q}%";
                $qq->where('name', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('tags', 'like', $like);
                return;
            }
            foreach ($terms as $t) {
                $like = "%{$t}%";
                $qq->orWhere('name', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('tags', 'like', $like);
            }
        });
    }

    // Optional price range filter
    $minPrice = request()->filled('min') ? (float) request('min') : null;
    $maxPrice = request()->filled('max') ? (float) request('max') : null;
    if (!is_null($minPrice) && !is_null($maxPrice) && $minPrice > $maxPrice) {
        [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
    }
    if (!is_null($minPrice)) {
        $productQuery->where('price', '>=', $minPrice);
    }
    if (!is_null($maxPrice)) {
        $productQuery->where('price', '<=', $maxPrice);
    }

    // Sorting
    $sort = request('sort', 'latest');
    switch ($sort) {
        case 'price_asc':
            $productQuery->orderBy('price', 'asc')->orderByDesc('id');
            break;
        case 'price_desc':
            $productQuery->orderBy('price', 'desc')->orderByDesc('id');
            break;
        case 'popular':
            $productQuery->withCount('views')
                ->orderByDesc('views_count')
                ->orderByDesc('id');
            break;
        case 'latest':
        default:
            $productQuery->latest();
            break;
    }

    $perPage = (int) request('per_page', 24);
    if (!in_array($perPage, [12, 24, 48], true)) {
        $perPage = 24;
    }

    // Paginate products under this category or any of its descendants
    $products = $productQuery
        ->paginate($perPage)
        ->appends(request()->query());

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
          'digital'  => 'digital',
        ];
        $listingType = $map[$type] ?? null;
        $fallbackUsed = false;
        if (! $listingType) {
            // Be forgiving: unknown type -> return all categories (id, name) so UI degrades gracefully
            $fallbackUsed = true;
            return response()->json(
                Category::query()->orderBy('name')->get(['id','name'])
            )->header('X-Categories-Fallback', '1');
        }

    // Include both parents and children relevant to the selected type:
    // - Any parent whose own listing_type matches, OR has at least one child matching
    // - Any child whose listing_type matches
    $parents = Category::query()
        ->whereNull('parent_id')
        ->where(function ($q) use ($listingType) {
            $q->where('listing_type', $listingType)
              ->orWhereHas('children', function ($qq) use ($listingType) {
                  $qq->where('listing_type', $listingType);
              });
        })
        ->orderBy('name')
        ->get(['id','name','parent_id']);

    $children = Category::query()
        ->whereNotNull('parent_id')
        ->where('listing_type', $listingType)
        ->orderBy('name')
        ->get(['id','name','parent_id']);

        $categories = $parents->concat($children)
            ->unique('id')
            ->values();

        // Fallback: if none matched (e.g., data not tagged yet), return all so seller can proceed
        if ($categories->isEmpty()) {
            $fallbackUsed = true;
            $categories = Category::query()->orderBy('name')->get(['id','name','parent_id']);
        }

        return response()
            ->json($categories)
            ->header('X-Categories-Fallback', $fallbackUsed ? '1' : '0');
    }

    /**
     * Admin: Bulk update selected categories' fields.
     * Allows updating listing_fee, listing_type, and listing_frequency.
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:categories,id',
            'listing_fee' => 'nullable|numeric|min:0',
            'listing_type' => 'nullable|in:products,services,digital',
            'listing_frequency' => 'nullable|in:1,4',
        ]);

        $payload = [];
        if ($request->filled('listing_fee')) {
            $payload['listing_fee'] = $request->input('listing_fee');
        }
        if ($request->filled('listing_type')) {
            $payload['listing_type'] = $request->input('listing_type');
        }
        if ($request->filled('listing_frequency')) {
            $payload['listing_frequency'] = (int) $request->input('listing_frequency');
        }

        if (empty($payload)) {
            return back()->with('warning', 'No changes selected. Choose at least one field.');
        }

        Category::whereIn('id', $validated['ids'])->update($payload);

        return back()->with('success', 'Selected categories updated successfully.');
    }

    /**
     * Admin: Bulk move categories to a new parent.
     */
    public function bulkMove(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:categories,id',
            'parent_id' => 'nullable|integer|exists:categories,id',
        ]);

        $ids = collect($validated['ids'])->unique()->values();
        $parentId = $request->input('parent_id');

        // Do not allow assigning parent to one of the selected IDs
        if ($parentId && $ids->contains((int) $parentId)) {
            return back()->with('warning', 'Cannot move categories under one of the selected items.');
        }

        // Basic move; note: does not detect deep cycles (moving into own descendant)
        // For now, we trust admin to avoid that; otherwise requires a tree walk.
        Category::whereIn('id', $ids)->update(['parent_id' => $parentId ?: null]);

        return back()->with('success', 'Selected categories moved successfully.');
    }

}
