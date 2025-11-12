<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Review;
use Illuminate\Support\Facades\Storage;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\SellerNewReviewMail;

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
            'rating'       => 'required|integer|min:1|max:5',
            'comment'      => 'nullable|string|max:2000',
            'photo'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'remove_photo' => 'nullable|boolean',
        ]);

        abort_if($item->review, 409, 'You have already reviewed this item.');

        // Gate digital products until download is confirmed
        if (optional($item->product)->type === 'digital') {
            if (empty($item->downloaded_at)) {
                return back()->with('warning', 'Please download your digital item before leaving a review.')
                            ->withInput();
            }
        }

        // Gate physical products until delivered
        if (optional($item->product)->type === 'physical') {
            if ($order->status !== \App\Models\Order::STATUS_DELIVERED) {
                return back()->with('warning', 'You can review this item after it is delivered.')
                            ->withInput();
            }
        }

        $data['shop_id'] = $item->product->shop_id;

        // Optional image upload
        $imagePath = null;
        if ($request->hasFile('photo')) {
            $imagePath = $request->file('photo')->store('review-images', 'public');
        }

        $review = $order->reviews()->create(array_merge(
            $data,
            ['order_item_id' => $item->id],
            $imagePath ? ['image_path' => $imagePath] : []
        ));

        // Notify the seller (shop owner) about the new review
        try {
            $shop = optional($item->product)->shop;
            $sellerId = (int) optional($shop)->user_id;
            if ($sellerId > 0) {
                $activity = Activity::create([
                    'user_id'      => $sellerId,
                    'is_read'      => false,
                    'type'         => Activity::TYPE_PRODUCT,
                    'related_id'   => $review->id,
                    'related_type' => Review::class,
                    'description'  => sprintf(
                        'New %d★ review on %s',
                        (int) $review->rating,
                        optional($item->product)->name ?: 'your listing'
                    ),
                    'link'         => route('seller.reviews.index'),
                    'causer_id'    => (int) Auth::id(),
                    'causer_type'  => User::class,
                    'properties'   => [
                        'order_id'     => (int) $order->id,
                        'order_item_id'=> (int) $item->id,
                        'product_id'   => (int) optional($item->product)->id,
                        'shop_id'      => (int) optional($shop)->id,
                        'rating'       => (int) $review->rating,
                    ],
                ]);

                // Send email to the seller
                $seller = \App\Models\User::find($sellerId);
                if ($seller && !empty($seller->email)) {
                    Mail::to($seller->email)->send(new SellerNewReviewMail($review, $seller));
                }
            }
        } catch (\Throwable $e) {
            // Fail silently; notification should not block review creation
        }

        return back()->with('success', 'Thank you for your review!');
    }
    
    /**
     * Update an existing review. Buyers may only increase their rating, not decrease it.
     */
    public function update(Request $request, Order $order, OrderItem $item, Review $review)
    {
        // Ensure the review belongs to the given order + item
        abort_if((int)$review->order_id !== (int)$order->id, 404);
        abort_if((int)$review->order_item_id !== (int)$item->id, 404);

        // Ensure current user owns the order
        abort_if((int)$order->user_id !== (int)Auth::id(), 403);

        $data = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
            'photo'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        // Enforce upgrade-only rating change
        $old = (int) $review->rating;
        $new = (int) $data['rating'];
        if ($new < $old) {
            return back()->withErrors(['rating' => 'You can only increase your original rating.'])->withInput();
        }

        // Apply updates (allow comment change either way)
        $payload = [
            'rating'  => $new,
            'comment' => $data['comment'] ?? $review->comment,
        ];

        $shouldDeleteExisting = $request->boolean('remove_photo') || $request->hasFile('photo');
        if ($shouldDeleteExisting && !empty($review->image_path)) {
            Storage::disk('public')->delete($review->image_path);
            $payload['image_path'] = null;
        }

        if ($request->hasFile('photo')) {
            $payload['image_path'] = $request->file('photo')->store('review-images', 'public');
        }

        $review->update($payload);

        return back()->with('success', 'Your review has been updated.');
    }
}
