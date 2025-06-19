<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Offer;

class OfferController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $shop = $user->shop;

        // If the seller doesn't have a shop, handle gracefully
        if (!$shop) {
            return redirect()->route('seller.shop.create')
                ->with('warning', 'Please create a shop to view offers.');
        }

        // Get all product IDs for this shop
        $productIds = $shop->products()->pluck('id');

        // Get all offers for these products
        $offers = Offer::whereIn('product_id', $productIds)->get();
        return view('seller.offers.index', compact('offers'));
    }

    public function create()
    {
        // Show form to create a new offer
        return view('seller.offers.create');
    }

    public function store(Request $request)
    {
        // Store a new offer
        // ...
        return redirect()->route('seller.offers.index')->with('success', 'Offer created successfully.');
    }

    public function show($id)
    {
        $offer = Offer::with(['product', 'buyer'])->findOrFail($id);
        return view('seller.offers.show', compact('offer'));
    }

    public function edit($id)
    {
        // Show form to edit an offer
        return view('seller.offers.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        // Update the offer
        // ...
        return redirect()->route('seller.offers.index')->with('success', 'Offer updated successfully.');
    }

    public function destroy($id)
    {
        // Delete the offer
        // ...
        return redirect()->route('seller.offers.index')->with('success', 'Offer deleted successfully.');
    }
} 