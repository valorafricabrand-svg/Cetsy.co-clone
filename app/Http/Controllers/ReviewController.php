<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
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

        $order->reviews()->create(array_merge(
            $data,
            ['order_item_id' => $item->id]
        ));

        return back()->with('success', 'Thank you for your review!');
    }
}
