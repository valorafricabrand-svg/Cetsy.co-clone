<?php

// app/Http/Controllers/ShippingProfileController.php

namespace App\Http\Controllers;

use App\Models\ShippingProfile;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShippingProfileController extends Controller
{
    public function index()
    {
        $shop = Auth::user()->shop;
        if (!$shop) {
            return redirect()->route('shops.create')
                             ->with('error', 'Please create a shop first.');
        }

        $profiles = ShippingProfile::where('shop_id', $shop->id)
                                   ->with('country')
                                   ->paginate(10);

        return view('shipping_profiles.index', compact('profiles'));
    }

    public function create()
    {
        $countries = Country::orderBy('name')->get();
        return view('shipping_profiles.create', compact('countries'));
    }

    public function store(Request $request)
    {
        $shop = Auth::user()->shop;
        if (!$shop) {
            return redirect()->route('shops.create')
                             ->with('error', 'Please create a shop first.');
        }

        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'country_id'       => 'required|exists:countries,id',
            'base_rate'        => 'required|numeric|min:0',
            'delivery_days'    => 'required|integer|min:0',
            'pickup_available' => 'nullable|boolean',
        ]);

        $data['shop_id'] = $shop->id;
        ShippingProfile::create($data);

        return redirect()->route('seller.shipping_profiles.index')
                         ->with('success', 'Shipping profile created successfully.');
    }

    public function edit(ShippingProfile $shippingProfile)
    {
        $this->authorizeShop($shippingProfile);
        $countries = Country::orderBy('name')->get();
        return view('shipping_profiles.edit', compact('shippingProfile', 'countries'));
    }

    public function update(Request $request, ShippingProfile $shippingProfile)
    {
        $this->authorizeShop($shippingProfile);

        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'country_id'       => 'required|exists:countries,id',
            'base_rate'        => 'required|numeric|min:0',
            'delivery_days'    => 'required|integer|min:0',
            'pickup_available' => 'nullable|boolean',
        ]);

        $shippingProfile->update($data);

        return redirect()->route('seller.shipping_profiles.index')
                         ->with('success', 'Shipping profile updated successfully.');
    }

    public function destroy(ShippingProfile $shippingProfile)
    {
        $this->authorizeShop($shippingProfile);
        $shippingProfile->delete();

        return redirect()->route('seller.shipping_profiles.index')
                         ->with('success', 'Shipping profile deleted successfully.');
    }

    protected function authorizeShop(ShippingProfile $profile)
    {
        $shop = Auth::user()->shop;
        if (!$shop || $profile->shop_id !== $shop->id) {
            abort(403, 'Unauthorized action.');
        }
    }
}
