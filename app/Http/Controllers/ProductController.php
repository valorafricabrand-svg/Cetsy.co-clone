<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductVariant;
use App\Models\ListingFeeType;
use App\Models\Country;
use App\Models\Wishlist;
use App\Models\ProcessingTime;
use App\Services\Shared\GetShippingService;
use App\Models\ShippingPeriod;

class ProductController extends Controller
{
  

    public function index(Request $request)
    {
        $user = Auth::user();

        // Ensure the user has a shop first
        if (! $user->shop) {
            return redirect()
                ->route('shops.create')
                ->with('error', 'You need to create a shop before adding products.');
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
        $products = $query->where('type', 'product')->paginate(20)->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $user = auth()->user();
        if ($user->shop()->count() <= 0) {
            session()->put(['intend_url' => 'shop/create']);
            return redirect(route('shops.create'))->with('message', 'To add product first create shop');
        }
        $shops = $user->shop;
        $shop = $shops->first();
        $countryOriginName = $shop ? Country::where('id', $shop->country)->value('name') : '';
        $categories = Category::orderBy('name')->get();
        $category_listFee_types = ListingFeeType::orderBy('id', 'asc')->get();
        $countries = Country::orderBy('id', 'asc')->get();
        $processing_times = ProcessingTime::all();
        $shippinService = (new GetShippingService())->handle();
        $shippingPeriods = ShippingPeriod::all();
        $shipping_type = 1;
        $shipping_type_other = 1;
        $itemReturnString = "I don't accept returns of this item";
        $itemExchangeString = "I don't accept exchanges of this item";
        $item_exchange = false;
        $item_return = false;
        $shippingChargeType = [
            ['id' => 0, 'name' => 'Free Shipping'],
            ['id' => 1, 'name' => 'Fixed Price'],
        ];
        $returnDeliveryDays = [
            ['id' => 7, 'name' => '7 days from delivery'],
            ['id' => 14, 'name' => '14 days from delivery'],
            ['id' => 21, 'name' => '21 days from delivery'],
            ['id' => 30, 'name' => '30 days from delivery'],
            ['id' => 45, 'name' => '45 days from delivery'],
            ['id' => 60, 'name' => '60 days from delivery'],
            ['id' => 90, 'name' => '90 days from delivery'],
        ];
        $shippingChargeType = [
            ['id' => 0, 'name' => 'Free Shipping'],
            ['id' => 1, 'name' => 'Fixed Price'],
        ];
        $shippingChargeType = [
            ['id' => 0, 'name' => 'Free Shipping'],
            ['id' => 1, 'name' => 'Fixed Price'],
        ];
        return view('products.create', compact(
            'categories',
            'category_listFee_types',
            'countries',
            'shippingChargeType',
            'returnDeliveryDays',
            'shippingChargeType',
            'processing_times',
            'countryOriginName',
            'shippinService',
            'shippingChargeType',
            'returnDeliveryDays',
            'shippingChargeType',
            'processing_times',
            'countryOriginName',
            'shippinService',
            'shippingPeriods',
            'shipping_type',
            'shipping_type_other',
            'itemReturnString',
            'itemExchangeString',
            'item_exchange',
            'item_return',
        ));
    }

    public function store(Request $request)
    {
        \DB::beginTransaction();
        try {
            $rules = [
                'name'        => 'required|string|max:255',
                'slug'        => 'nullable|string|max:255|unique:products,slug',
                'category_id' => 'required|exists:categories,id',
                'description' => 'nullable|string',
                'price'       => 'required|numeric|min:0',
                'discount_price' => 'nullable|numeric|min:0',
                'stock'       => 'required|integer|min:0',
                'low_stock'   => 'nullable|integer|min:0',
                'status'      => 'nullable|in:draft,active,archived',
                'product_type'=> 'required|in:physical,digital',
                'condition'   => 'required|in:new,refurbished,used',
                'images.*'    => 'nullable|image|max:2048',
                // Digital fields
                'download_file'   => 'nullable|file|mimes:pdf,zip,rar,epub,mobi,exe,dmg,mp3,mp4,avi,mov,doc,docx,xls,xlsx,ppt,pptx',
                'download_limit'  => 'nullable|integer|min:1',
                'access_expiry'   => 'nullable|integer|min:1',
                // New fields
                'renewal_option'  => 'required|in:0,1',
                'listTypeFee_id'  => 'required|exists:listing_fee_types,id',
                'variation_one_name' => 'nullable|string|max:255',
                'variation_two_name' => 'nullable|string|max:255',
                'variationOneOptions' => 'nullable|array',
                'variationOneOptions.*.title' => 'nullable|string|max:255',
                'variationOneOptions.*.price' => 'nullable|numeric|min:0',
                'variationTwoOptions' => 'nullable|array',
                'variationTwoOptions.*.title' => 'nullable|string|max:255',
                'variationTwoOptions.*.price' => 'nullable|numeric|min:0',
                // Shipping fields
                'origin_id' => 'required|exists:countries,id',
                'origin_postal_code' => 'nullable|string|max:50',
                'processing_time_id' => 'required|exists:processing_times,id',
                'local_shipping_service_id' => 'nullable|integer',
                'local_shipping_service_other' => 'nullable|string|max:255',
                'localshippingPeriod_id' => 'nullable|integer',
                'local_default_shipping_price' => 'nullable|numeric|min:0',
                'local_shipping_price' => 'nullable|numeric|min:0',
                'shipping_type' => 'nullable|integer',
                'international_shipping_service_id' => 'nullable|integer',
                'international_shipping_service_other' => 'nullable|string|max:255',
                'internationalshippingPeriod_id' => 'nullable|integer',
                'default_shipping_price' => 'nullable|numeric|min:0',
                'shipping_price' => 'nullable|numeric|min:0',
                'shipping_type_other' => 'nullable|integer',
                // Return/exchange fields
                'item_return' => 'nullable|boolean',
                'item_exchange' => 'nullable|boolean',
                'total_return_days' => 'nullable|integer',
            ];

            // If digital, require digital fields
            if ($request->product_type === 'digital') {
                $rules['download_file'] = 'required|file|mimes:pdf,zip,rar,epub,mobi,exe,dmg,mp3,mp4,avi,mov,doc,docx,xls,xlsx,ppt,pptx';
                $rules['download_limit'] = 'required|integer|min:1';
                $rules['access_expiry'] = 'required|integer|min:1';
            }

            $data = $request->validate($rules);

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
                if (Product::where('slug', $data['slug'])->exists()) {
                    $data['slug'] .= '-' . time();
                }
            }

            $data['shop_id'] = Auth::user()->shop->id;

            // Handle digital file upload
            if ($request->hasFile('download_file')) {
                $data['download_file'] = $request->file('download_file')->store('products/digital', 'public');
            }

            // Save product
            $product = Product::create($data);

            // Handle images
            if ($files = $request->file('images')) {
                foreach ($files as $img) {
                    $path = $img->store('products/images','public');
                    $product->media()->create([
                        'type' => 'image',
                        'url'  => $path,
                    ]);
                }
            }

            // Handle variations (Variation One & Two)
            if ($request->filled('variationOneOptions') || $request->filled('variationTwoOptions')) {
                $variationOneName = $request->input('variation_one_name');
                $variationTwoName = $request->input('variation_two_name');
                $variationOneOptions = $request->input('variationOneOptions', []);
                $variationTwoOptions = $request->input('variationTwoOptions', []);
                // Save as JSON or in a related table as needed
                $product->variation_one_name = $variationOneName;
                $product->variation_two_name = $variationTwoName;
                $product->variation_one_options = json_encode($variationOneOptions);
                $product->variation_two_options = json_encode($variationTwoOptions);
                $product->save();
            }

            // Save shipping and return/exchange fields
            $product->renewal_option = $request->input('renewal_option');
            $product->listTypeFee_id = $request->input('listTypeFee_id');
            $product->origin_id = $request->input('origin_id');
            $product->origin_postal_code = $request->input('origin_postal_code');
            $product->processing_time_id = $request->input('processing_time_id');
            $product->local_shipping_service_id = $request->input('local_shipping_service_id');
            $product->local_shipping_service_other = $request->input('local_shipping_service_other');
            $product->localshippingPeriod_id = $request->input('localshippingPeriod_id');
            $product->local_default_shipping_price = $request->input('local_default_shipping_price');
            $product->local_shipping_price = $request->input('local_shipping_price');
            $product->shipping_type = $request->input('shipping_type');
            $product->international_shipping_service_id = $request->input('international_shipping_service_id');
            $product->international_shipping_service_other = $request->input('international_shipping_service_other');
            $product->internationalshippingPeriod_id = $request->input('internationalshippingPeriod_id');
            $product->default_shipping_price = $request->input('default_shipping_price');
            $product->shipping_price = $request->input('shipping_price');
            $product->shipping_type_other = $request->input('shipping_type_other');
            $product->item_return = $request->input('item_return', false);
            $product->item_exchange = $request->input('item_exchange', false);
            $product->total_return_days = $request->input('total_return_days');
            $product->type = 'product';
            $product->save();

            \DB::commit();
            return redirect()
                ->route('products.index')
                ->with('success', 'Product created successfully!');
        } catch (\Throwable $e) {
            \DB::rollBack();
            \Log::error('Product store failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);
            return back()->with('error', 'An error occurred while creating the product. Please try again later.');
        }
    }

    public function edit(Product $product)
    {
        abort_if($product->shop_id !== Auth::user()->shop->id, 403);

        $user = auth()->user();
        $shops = $user->shop;
        $shop = $shops->first();
        $countryOriginName = $shop ? Country::where('id', $shop->country)->value('name') : '';
        $categories = Category::orderBy('name')->get();
        $category_listFee_types = ListingFeeType::orderBy('id', 'asc')->get();
        $countries = Country::orderBy('id', 'asc')->get();
        $processing_times = ProcessingTime::all();
        $shippinService = (new GetShippingService())->handle();
        $shippingPeriods = ShippingPeriod::all();
        $shipping_type = $product->shipping_type ?? 1;
        $shipping_type_other = $product->shipping_type_other ?? 1;
        $itemReturnString = $product->item_return ? "I accept returns of this item" : "I don't accept returns of this item";
        $itemExchangeString = $product->item_exchange ? "I accept exchanges of this item" : "I don't accept exchanges of this item";
        $item_exchange = $product->item_exchange ?? false;
        $item_return = $product->item_return ?? false;
        $shippingChargeType = [
            ['id' => 0, 'name' => 'Free Shipping'],
            ['id' => 1, 'name' => 'Fixed Price'],
        ];
        $returnDeliveryDays = [
            ['id' => 7, 'name' => '7 days from delivery'],
            ['id' => 14, 'name' => '14 days from delivery'],
            ['id' => 21, 'name' => '21 days from delivery'],
            ['id' => 30, 'name' => '30 days from delivery'],
            ['id' => 45, 'name' => '45 days from delivery'],
            ['id' => 60, 'name' => '60 days from delivery'],
            ['id' => 90, 'name' => '90 days from delivery'],
        ];

        return view('products.edit', compact(
            'product',
            'categories',
            'category_listFee_types',
            'countries',
            'shippingChargeType',
            'returnDeliveryDays',
            'processing_times',
            'countryOriginName',
            'shippinService',
            'shippingPeriods',
            'shipping_type',
            'shipping_type_other',
            'itemReturnString',
            'itemExchangeString',
            'item_exchange',
            'item_return'
        ));
    }

    public function update(Request $request, Product $product)
    {
        \DB::beginTransaction();
        try {
            abort_if($product->shop_id !== Auth::user()->shop->id, 403);

            $rules = [
                'name'        => 'required|string|max:255',
                'slug'        => "nullable|string|max:255|unique:products,slug,{$product->id}",
                'category_id' => 'required|exists:categories,id',
                'description' => 'nullable|string',
                'price'       => 'required|numeric|min:0',
                'discount_price' => 'nullable|numeric|min:0',
                'stock'       => 'required|integer|min:0',
                'low_stock'   => 'nullable|integer|min:0',
                // 'status'      => 'nullable|in:draft,active,archived',
                'product_type'=> 'required|in:physical,digital',
                'condition'   => 'required|in:new,refurbished,used',
                'images.*'    => 'nullable|image|max:2048',
                // Digital fields
                'download_file'   => 'nullable|file|mimes:pdf,zip,rar,epub,mobi,exe,dmg,mp3,mp4,avi,mov,doc,docx,xls,xlsx,ppt,pptx',
                'download_limit'  => 'nullable|integer|min:1',
                'access_expiry'   => 'nullable|integer|min:1',
                // New fields
                'renewal_option'  => 'required|in:0,1',
                'listTypeFee_id'  => 'required|exists:listing_fee_types,id',
                'variation_one_name' => 'nullable|string|max:255',
                'variation_two_name' => 'nullable|string|max:255',
                'variationOneOptions' => 'nullable|array',
                'variationOneOptions.*.title' => 'nullable|string|max:255',
                'variationOneOptions.*.price' => 'nullable|numeric|min:0',
                'variationTwoOptions' => 'nullable|array',
                'variationTwoOptions.*.title' => 'nullable|string|max:255',
                'variationTwoOptions.*.price' => 'nullable|numeric|min:0',
                // Shipping fields
                'origin_id' => 'required|exists:countries,id',
                'origin_postal_code' => 'nullable|string|max:50',
                'processing_time_id' => 'required|exists:processing_times,id',
                'local_shipping_service_id' => 'nullable|integer',
                'local_shipping_service_other' => 'nullable|string|max:255',
                'localshippingPeriod_id' => 'nullable|integer',
                'local_default_shipping_price' => 'nullable|numeric|min:0',
                'local_shipping_price' => 'nullable|numeric|min:0',
                'shipping_type' => 'nullable|integer',
                'international_shipping_service_id' => 'nullable|integer',
                'international_shipping_service_other' => 'nullable|string|max:255',
                'internationalshippingPeriod_id' => 'nullable|integer',
                'default_shipping_price' => 'nullable|numeric|min:0',
                'shipping_price' => 'nullable|numeric|min:0',
                'shipping_type_other' => 'nullable|integer',
                // Return/exchange fields
                'item_return' => 'nullable|boolean',
                'item_exchange' => 'nullable|boolean',
                'total_return_days' => 'nullable|integer',
            ];

            // If digital, require digital fields
            if ($request->product_type === 'digital') {
                $rules['download_file'] = 'required|file|mimes:pdf,zip,rar,epub,mobi,exe,dmg,mp3,mp4,avi,mov,doc,docx,xls,xlsx,ppt,pptx';
                $rules['download_limit'] = 'required|integer|min:1';
                $rules['access_expiry'] = 'required|integer|min:1';
            }

            $data = $request->validate($rules);

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
                if (Product::where('slug', $data['slug'])->where('id', '!=', $product->id)->exists()) {
                    $data['slug'] .= '-' . time();
                }
            }

            // Handle digital file upload
            if ($request->hasFile('download_file')) {
                // Delete old file if exists
                if ($product->download_file) {
                    Storage::disk('public')->delete($product->download_file);
                }
                $data['download_file'] = $request->file('download_file')->store('products/digital', 'public');
            }

            // Update product
            $product->update($data);

            // Handle images
            if ($files = $request->file('images')) {
                foreach ($files as $img) {
                    $path = $img->store('products/images','public');
                    $product->media()->create([
                        'type' => 'image',
                        'url'  => $path,
                    ]);
                }
            }

            // Handle variations (Variation One & Two)
            if ($request->filled('variationOneOptions') || $request->filled('variationTwoOptions')) {
                $variationOneName = $request->input('variation_one_name');
                $variationTwoName = $request->input('variation_two_name');
                $variationOneOptions = $request->input('variationOneOptions', []);
                $variationTwoOptions = $request->input('variationTwoOptions', []);
                
                $product->variation_one_name = $variationOneName;
                $product->variation_two_name = $variationTwoName;
                $product->variation_one_options = json_encode($variationOneOptions);
                $product->variation_two_options = json_encode($variationTwoOptions);
                $product->save();
            }

            // Update shipping and return/exchange fields
            $product->renewal_option = $request->input('renewal_option');
            $product->listTypeFee_id = $request->input('listTypeFee_id');
            $product->origin_id = $request->input('origin_id');
            $product->origin_postal_code = $request->input('origin_postal_code');
            $product->processing_time_id = $request->input('processing_time_id');
            $product->local_shipping_service_id = $request->input('local_shipping_service_id');
            $product->local_shipping_service_other = $request->input('local_shipping_service_other');
            $product->localshippingPeriod_id = $request->input('localshippingPeriod_id');
            $product->local_default_shipping_price = $request->input('local_default_shipping_price');
            $product->local_shipping_price = $request->input('local_shipping_price');
            $product->shipping_type = $request->input('shipping_type');
            $product->international_shipping_service_id = $request->input('international_shipping_service_id');
            $product->international_shipping_service_other = $request->input('international_shipping_service_other');
            $product->internationalshippingPeriod_id = $request->input('internationalshippingPeriod_id');
            $product->default_shipping_price = $request->input('default_shipping_price');
            $product->shipping_price = $request->input('shipping_price');
            $product->shipping_type_other = $request->input('shipping_type_other');
            $product->item_return = $request->input('item_return', false);
            $product->item_exchange = $request->input('item_exchange', false);
            $product->total_return_days = $request->input('total_return_days');
            $product->save();

            \DB::commit();
            return redirect()
                ->route('products.index')
                ->with('success', 'Product updated successfully!');
        } catch (\Throwable $e) {
            \DB::rollBack();
            \Log::error('Product update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'product_id' => $product->id ?? null,
            ]);
            return back()->with('error', 'An error occurred while updating the product. Please try again later.');
        }
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


        public function listing($slug)
    {

        $product = Product::whereSlug($slug)->first();
          $product->views()->create([
        'viewer_id' => auth()->id(),
        'ip'        => request()->ip(),
    ]);
        return view('theme.listing_show', compact('product'));
        
    }


       public function wishlist()
    {
        $wishlistItems = Wishlist::where('user_id', Auth::id())->get();

       

        return view('buyer.wishlist', compact('wishlistItems'));
    }


    public function listings()
{
    $products = Product::with('media')->latest()->paginate(16);
    return view('theme.listings', compact('products'));
}


  public function search(Request $request)
    {
        $q = $request->input('q');

        $products = Product::where('name', 'like', "%{$q}%")
                           ->orWhere('description', 'like', "%{$q}%")
                           ->paginate(12);

        return view('theme.listings', compact('products'))->with('q', $q);
        
    }

}
