<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shop;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Show all reviews for a shop.
     */
    public function shopReviews(Shop $shop)
    {
        $reviews = $shop->reviews()
            ->with(['user', 'orderItem.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $averageRating = $shop->reviews()->avg('rating') ?? 0;
        $reviewCount = $shop->reviews()->count();

        return view('shops.reviews', compact('shop', 'reviews', 'averageRating', 'reviewCount'));
    }

    /**
     * Store a new review for a single order item.
     */
    public function store(Request $request, Order $order, OrderItem $item)
    {
        

        $data = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        abort_if($item->review, 409, 'You have already reviewed this item.');

        $data['shop_id'] = $item->product->shop_id;

        $order->reviews()->create(array_merge(
            $data,
            ['order_item_id' => $item->id]
        ));

        return back()->with('success', 'Thank you for your review!');
    }
}
