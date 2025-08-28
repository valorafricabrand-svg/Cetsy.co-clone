<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display a listing of all reviews.
     */
    public function index(Request $request)
    {
        $reviews = Review::with(['shop', 'user', 'order'])
            ->orderByDesc('id')
            ->paginate(20);
        return view('admin.reviews.index', compact('reviews'));
    }

    /**
     * Delete a review.
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $review = \App\Models\Review::findOrFail($id);
        $review->delete();

        return redirect()->route('admin.reviews.index')->with('success', 'Review deleted successfully.');
    }

    /**
     * Approve the specified review.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approve($id)
    {
        $review = Review::findOrFail($id);
        $review->update([
            'approved' => true,
            'approved_at' => now(),
            'approved_by' => auth()->id()
        ]);

        return back()->with('success', 'Review has been approved.');
    }

    /**
     * Store a newly created review.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $orderId, $itemId)
{
    // Current time and user info for logging
    $currentDateTime = now()->format('Y-m-d H:i:s');
    $currentUser = auth()->user() ? auth()->user()->name : 'HK-MBURU';
    
    // Log method entry
    \Log::info("[ReviewController] Store method started", [
        'time' => $currentDateTime,
        'user' => $currentUser,
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent(),
        'order_id' => $orderId,
        'item_id' => $itemId
    ]);
    
    try {
        // Get the order item
        $orderItem = \App\Models\OrderItem::findOrFail($itemId);
        
        // Get the shop_id from the product or order
        $shopId = null;
        if ($orderItem->product && $orderItem->product->shop_id) {
            $shopId = $orderItem->product->shop_id;
        } elseif ($orderItem->order && $orderItem->order->shop_id) {
            $shopId = $orderItem->order->shop_id;
        }
        
        \Log::info("[ReviewController] Got shop_id from order item", [
            'shop_id' => $shopId,
            'order_item_id' => $itemId
        ]);
        
        if (!$shopId) {
            throw new \Exception("Cannot determine shop ID for this order item");
        }
        
        // Validate the incoming request
        \Log::info("[ReviewController] Starting validation");
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);
        \Log::info("[ReviewController] Validation passed successfully");
        
        // Prepare review data
        $reviewData = [
            'shop_id' => $shopId,
            'user_id' => auth()->id(),
            'order_id' => $orderId,
            'order_item_id' => $itemId,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'approved' => false, // Default to not approved
            'created_at' => $currentDateTime,
            'updated_at' => $currentDateTime,
        ];
        
        // Create the review
        $review = Review::create($reviewData);
        
        // Log success and redirect
        \Log::info("[ReviewController] Review created successfully", [
            'review_id' => $review->id
        ]);
        
        return redirect()
            ->back()
            ->with('success', 'Your review has been submitted and is pending approval.');
            
    } catch (\Exception $e) {
        // Log error
        \Log::error("[ReviewController] Error creating review", [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'An error occurred while submitting your review. Please try again.');
    }
}
}