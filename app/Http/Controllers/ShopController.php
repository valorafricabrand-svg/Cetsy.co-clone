<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    /**
     * Show the “Create Your Shop” form, or redirect if the user already has a shop.
     */
    public function create()
    {
        if (Auth::user()->shop) {
            return redirect()->route('shops.show', Auth::user()->shop);
        }

        return view('shops.create');
    }

    /**
     * Validate and store a new shop.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            // 1) Shop preferences
            'language'         => 'required|string|in:English,Spanish,French',
            'country'          => 'required|string|in:United States,Canada,United Kingdom',
            'currency'         => 'required|string|in:USD,CAD,GBP',

            // 2) Name & slug
            'name'             => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:shops,slug',

            // 3) Payment details
            'bank_account'     => 'required|string|max:50',
            'routing_number'   => 'required|string|max:50',

            // 4) Billing info
            'address'          => 'required|string|max:255',
            'city'             => 'required|string|max:100',
            'postal'           => 'required|string|max:20',

          

            // Optional logo
            'logo'             => 'nullable|image|max:2048',
        ]);

        // Auto-generate slug if blank
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
            if (Shop::where('slug', $data['slug'])->exists()) {
                $data['slug'] .= '-' . time();
            }
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')
                                   ->store('shops/logos', 'public');
        }

        // We don't persist the password field
        unset($data['password']);

        // Ensure enable_2fa is boolean (checkbox may be absent)
        $data['enable_2fa'] = !empty($data['enable_2fa']);

        // Create the shop via the one-to-one relationship
        $shop = Auth::user()->shop()->create($data);

        return redirect()
            ->route('shops.show', $shop)
            ->with('success', 'Your shop has been created!');
    }

    /**
     * Display a single shop by slug.
     */
    public function show(Shop $shop)
    {
        return view('shops.show', compact('shop'));
    }


   public function showPublic(Shop $shop)
{
    $products = $shop->products()
        ->with('media')      // Eager load product images
        ->latest()
        ->paginate(12);      // Paginate for frontend performance

    return view('theme.shop', compact('shop', 'products'));
}


    


    /**
 * Show the edit form.
 */
public function edit(Shop $shop)
{
  

    return view('shops.edit', compact('shop'));
}

/**
 * Persist updates from the edit form.
 */
public function update(Request $request, Shop $shop)
{
    // Authorization
    $this->authorize('update', $shop);

    // Validate just the editable fields
    $data = $request->validate([
        'language'       => 'required|string|in:English,Spanish,French',
        'country'        => 'required|string|in:United States,Canada,United Kingdom',
        'currency'       => 'required|string|in:USD,CAD,GBP',
        'name'           => 'required|string|max:255',
        'slug'           => 'required|string|max:255|unique:shops,slug,' . $shop->id,
        'bank_account'   => 'required|string|max:50',
        'routing_number' => 'required|string|max:50',
        'address'        => 'required|string|max:255',
        'city'           => 'required|string|max:100',
        'postal'         => 'required|string|max:20',
        'password'       => ['required','current_password'],
        'enable_2fa'     => ['required','boolean'],
        'logo'           => ['nullable','image','max:2048'],
    ]);

    // Slug uniqueness fallback if someone cleared it
    if (empty($data['slug'])) {
        $data['slug'] = Str::slug($data['name']);
        if (Shop::where('slug', $data['slug'])->where('id','!=',$shop->id)->exists()) {
            $data['slug'] .= '-' . time();
        }
    }

    // Logo upload
    if ($request->hasFile('logo')) {
        $data['logo'] = $request->file('logo')
                               ->store('shops/logos','public');
    }

    // Remove password from data
    unset($data['password']);

    // Update the model
    $shop->update($data);

    return redirect()
        ->route('shops.show', $shop)
        ->with('success','Shop updated successfully!');
}

}
