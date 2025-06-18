<?php

namespace App\Http\Controllers;

use App\Models\ShippingProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShippingProfileController extends Controller
{
    public function index()
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()->route('shops.create')->with('error', 'Please create a shop first.');
        }

        $profiles = ShippingProfile::where('shop_id', $shop->id)->paginate(10);

        return view('shipping_profiles.index', compact('profiles'));
    }

    public function create()
    {
        return view('shipping_profiles.create');
    }

    public function store(Request $request)
    {
        $shop = Auth::user()->shop;
        if (!$shop) {
            return redirect()->route('shops.create')->with('error', 'Please create a shop first.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:3',
            'base_rate' => 'required|numeric|min:0',
            'delivery_days' => 'required|integer|min:0',
            'pickup_available' => 'nullable|boolean',
        ]);

        $data['pickup_available'] = $request->has('pickup_available');
        $data['shop_id'] = $shop->id;

        ShippingProfile::create($data);

        return redirect()->route('shipping_profiles.index')->with('success', 'Shipping profile created successfully.');
    }

    public function edit(ShippingProfile $shippingProfile)
    {
        $this->authorizeShop($shippingProfile);

        return view('shipping_profiles.edit', compact('shippingProfile'));
    }

    public function update(Request $request, ShippingProfile $shippingProfile)
    {
        $this->authorizeShop($shippingProfile);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:3',
            'base_rate' => 'required|numeric|min:0',
            'delivery_days' => 'required|integer|min:0',
            'pickup_available' => 'nullable|boolean',
        ]);

        $data['pickup_available'] = $request->has('pickup_available');

        $shippingProfile->update($data);

        return redirect()->route('shipping_profiles.index')->with('success', 'Shipping profile updated successfully.');
    }

    public function destroy(ShippingProfile $shippingProfile)
    {
        $this->authorizeShop($shippingProfile);

        $shippingProfile->delete();

        return redirect()->route('shipping_profiles.index')->with('success', 'Shipping profile deleted successfully.');
    }

    // Authorization helper to check profile belongs to user's shop
    protected function authorizeShop(ShippingProfile $profile)
    {
        $shop = Auth::user()->shop;
        if (!$shop || $profile->shop_id !== $shop->id) {
            abort(403, 'Unauthorized action.');
        }
    }
}
