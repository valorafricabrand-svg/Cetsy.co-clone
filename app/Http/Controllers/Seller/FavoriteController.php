<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Product;
use App\Models\Activity;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $shop = $user->shop;

        // If the seller doesn't have a shop, handle gracefully
        if (!$shop) {
            return redirect()->route('seller.shop.create')
                ->with('warning', 'Please create a shop to view favorites.');
        }

        // Get all product IDs for this shop
        $productIds = $shop->products()->pluck('id');

        // Get all wishlist items for these products with buyer and product information
        $favorites = Wishlist::whereIn('product_id', $productIds)
            ->with(['product.media', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group favorites by product for better organization
        $favoritesByProduct = $favorites->groupBy('product_id');

        // Analytics: messages sent from Favorites (total and last 7 days)
        $favoritesMessagesTotal = Activity::where('user_id', $user->id)
            ->where('type', Activity::TYPE_MESSAGE)
            ->where('properties->source', 'favorites')
            ->count();
        $favoritesMessagesWeek = Activity::where('user_id', $user->id)
            ->where('type', Activity::TYPE_MESSAGE)
            ->where('properties->source', 'favorites')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        // Mark shop-favorite notifications as read for this seller without
        // clearing the user's own saved-favorites notifications.
        try {
            Activity::where('user_id', $user->id)
                ->where('type', Activity::TYPE_WISHLIST)
                ->where('is_read', false)
                ->whereNotNull('causer_id')
                ->whereIn('related_id', $productIds)
                ->update(['is_read' => true]);
        } catch (\Throwable $e) { /* noop */ }

        return view('seller.favorites.index', compact(
            'favorites',
            'favoritesByProduct',
            'favoritesMessagesTotal',
            'favoritesMessagesWeek'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
