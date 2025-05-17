<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
  

    public function index(Request $request)
    {
        $query = Product::with('category','media')
            ->where('shop_id', Auth::user()->shop->id)
            ->latest();

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $products = $query->paginate(20)->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:products,slug',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'status'      => 'required|in:draft,active,archived',
            'images.*'    => 'nullable|image|max:2048',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
            if (Product::where('slug', $data['slug'])->exists()) {
                $data['slug'] .= '-' . time();
            }
        }

        $data['shop_id'] = Auth::user()->shop->id;
        $product = Product::create($data);

        if ($files = $request->file('images')) {
            foreach ($files as $img) {
                $path = $img->store('products/images','public');
                $product->media()->create([
                    'type' => 'image',
                    'url'  => $path,
                ]);
            }
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Product created successfully!');
    }

    public function edit(Product $product)
    {
        abort_if($product->shop_id !== Auth::user()->shop->id, 403);

        $categories = Category::orderBy('name')->get();
        return view('products.edit', compact('product','categories'));
    }

    public function update(Request $request, Product $product)
    {
        abort_if($product->shop_id !== Auth::user()->shop->id, 403);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => "nullable|string|max:255|unique:products,slug,{$product->id}",
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'status'      => 'required|in:draft,active,archived',
            'images.*'    => 'nullable|image|max:2048',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $product->update($data);

        if ($files = $request->file('images')) {
            foreach ($files as $img) {
                $path = $img->store('products/images','public');
                $product->media()->create([
                    'type' => 'image',
                    'url'  => $path,
                ]);
            }
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        abort_if($product->shop_id !== Auth::user()->shop->id, 403);

        foreach ($product->media as $media) {
            Storage::disk('public')->delete($media->url);
            $media->delete();
        }

        $product->delete();

        return back()->with('success', 'Product deleted successfully!');
    }

    public function show(Product $product)
    {
        // Optional public view
        return view('products.show', compact('product'));
    }
}
