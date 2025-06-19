<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ListingFeeType;
use App\Models\Category;
use Illuminate\Support\Str;
use App\Models\Country;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Ensure the user has a shop first
        if (! $user->shop) {
            return redirect()
                ->route('shops.create')
                ->with('error', 'You need to create a shop before adding services.');
        }

        $shopId = $user->shop->id;

        // Build the query
        $query = Product::with(['category', 'media'])
            ->where('shop_id', $shopId)
            ->latest();

        // Apply search if provided
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Paginate & preserve query string
        $services = $query->where('type', 'service')->paginate(20)->withQueryString();
     
        return view('seller.services.index', compact('services'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $shopId = $user->shop->id;
        $category_listFee_types = ListingFeeType::orderBy('id', 'asc')->get();
        $categories = Category::orderBy('id', 'asc')->get();
        $countries = Country::orderBy('id', 'asc')->get();
        return view('seller.services.create', compact('category_listFee_types', 'categories', 'countries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $shopId = $user->shop->id;

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'email' => 'required|email|max:255',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'tags' => 'nullable|string|max:255',
            'origin_id' => 'required|string|max:255',
            'category_id' => 'required|string|max:255',
            'photos.*' => 'required|image|max:2048',
            'price_type' => 'required|string|max:255',
            'available_days' => 'required|array',
            'available_days.*' => 'required|string|max:255',
            'available_time_from' => 'required|string|max:255',
            'available_time_to' => 'required|string|max:255',
            'duration_value' => 'required|numeric|min:0',
            'duration_unit' => 'required|string|max:255',
            'is_remote' => 'nullable|in:0,1',
        ]);

        DB::beginTransaction();
        try {
            // Find category by id
            $category = Category::where('id', $data['category_id'])->first();

            // Generate slug from name
            $slug = Str::slug($data['name']);
            if (empty($slug) || Product::where('slug', $slug)->exists()) {
                $slug .= '-' . time();
            }


            $product = Product::create([
                'shop_id' => $shopId,
                'category_id' => $category ? $category->id : null,
                'name' => $data['name'],
                'price' => $data['price'],
                'origin_id' => $data['origin_id'],
                'slug' => $slug,
                'description' => $data['description'],
                'status' => 'draft',
                'type' => 'service',
                'phone' => $data['phone'],
                'email' => $data['email'],
                'location' => $data['location'],
                'tags' => $data['tags'] ?? null,
                'price_type' => $data['price_type'],
                'available_days' => $data['available_days'],
                'available_time_to' => $data['available_time_to'],
                'available_time_from' => $data['available_time_from'],
                'duration_value' => $data['duration_value'],
                'duration_unit' => $data['duration_unit'],
                'is_remote' => $data['is_remote'],
            ]);

            // Attach images
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $img) {
                    $path = $img->store('products/images', 'public');
                    $product->media()->create([
                        'type' => 'image',
                        'url' => $path,
                    ]);
                }
            }

            DB::commit();
            return redirect()
                ->route('seller.services.index')
                ->with('success', 'Service created successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Service creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
            ]);
            return back()->with('error', 'An error occurred while creating the service. Please try again later.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        // Optional public view
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = Auth::user();
        $service = Product::where('id', $id)->where('shop_id', $user->shop->id)->where('type', 'service')->with('media')->firstOrFail();
        $category_listFee_types = ListingFeeType::orderBy('id', 'asc')->get();
        $categories = Category::orderBy('id', 'asc')->get();
      
        $countries = Country::orderBy('id', 'asc')->get();
        return view('seller.services.edit', compact('service', 'category_listFee_types', 'categories', 'countries'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $service = Product::where('id', $id)->where('shop_id', $user->shop->id)->where('type', 'service')->firstOrFail();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'email' => 'required|email|max:255',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'tags' => 'nullable|string|max:255',
            'origin_id' => 'required|string|max:255',
            'category_id' => 'required|string|max:255',
            'renewal_option'  => 'required|in:0,1',
            'listTypeFee_id' => 'required|string|max:255',
            'photos.*' => 'nullable|image|max:2048',
            'price_type' => 'required|string|max:255',
            'available_days' => 'required|array',
            'available_days.*' => 'required|string|max:255',
            'available_time_from' => 'required|string|max:255',
            'available_time_to' => 'required|string|max:255',
            'duration_value' => 'required|numeric|min:0',
            'duration_unit' => 'required|string|max:255',
            'is_remote' => 'required|in:0,1',
        ]);

        DB::beginTransaction();
        try {
            $category = Category::where('id', $data['category_id'])->first();

            // Generate slug from name
            $slug = Str::slug($data['name']);
            if (empty($slug) || Product::where('slug', $slug)->where('id', '!=', $service->id)->exists()) {
                $slug .= '-' . time();
            }

            $service->update([
                'category_id' => $category ? $category->id : null,
                'name' => $data['name'],
                'price' => $data['price'],
                'origin_id' => $data['origin_id'],
                'slug' => $slug,
                'description' => $data['description'],
                'listTypeFee_id' => $data['listTypeFee_id'],
                'renewal_option' => $data['renewal_option'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'location' => $data['location'],
                'tags' => $data['tags'] ?? null,
                'price_type' => $data['price_type'],
                'available_days' => $data['available_days'],
                'available_time_to' => $data['available_time_to'],
                'available_time_from' => $data['available_time_from'],
                'duration_value' => $data['duration_value'],
                'duration_unit' => $data['duration_unit'],
                'is_remote' => $data['is_remote'],
            ]);

            // Attach new images if any
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $img) {
                    $path = $img->store('products/images', 'public');
                    $service->media()->create([
                        'type' => 'image',
                        'url' => $path,
                    ]);
                }
            }

            DB::commit();
            return redirect()
                ->route('seller.services.index', $service->id)
                ->with('success', 'Service updated successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Service update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'service_id' => $service->id,
            ]);
            return back()->with('error', 'An error occurred while updating the service. Please try again later.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $service = Product::where('id', $id)->where('shop_id', $user->shop->id)->where('type', 'service')->with('media')->firstOrFail();

        DB::beginTransaction();
        try {
            // Delete all related images from storage and database
            foreach ($service->media as $media) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($media->url);
                $media->delete();
            }
            $service->delete();
            DB::commit();
            return redirect()->route('seller.services.index')->with('success', 'Service deleted successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Service delete failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'service_id' => $id,
            ]);
            return back()->with('error', 'An error occurred while deleting the service. Please try again later.');
        }
    }
}
