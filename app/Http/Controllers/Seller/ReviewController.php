<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\BuyerReviewResponseMail;
use App\Models\Activity;
use App\Models\User;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $seller = Auth::user();
        $shop = $seller->shop;

        if (! $shop) {
            abort(403, 'No shop assigned to your account.');
        }

        $search = trim((string) $request->input('q', ''));
        $ratingFilter = $request->filled('rating') ? (int) $request->input('rating') : null;
        $perPage = (int) $request->input('per_page', 15);
        $perPage = $perPage > 0 ? min($perPage, 100) : 15;

        $baseQuery = Review::with([
                // Load related order and listing product
                'order',
                'orderItem.product' => function ($query) {
                    $query->select('id', 'name', 'type', 'slug');
                },
                // Qualify user columns to avoid ambiguous id
                'user' => function ($query) {
                    $query->select('users.id', 'users.name');
                },
            ])
            ->where('shop_id', $shop->id);

        if ($search !== '') {
            $baseQuery->where(function ($query) use ($search) {
                $query->where('comment', 'like', "%{$search}%")
                    ->orWhereHas('orderItem.product', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('order', function ($sub) use ($search) {
                        $sub->where('id', 'like', "%{$search}%");
                    });
            });
        }

        if (! is_null($ratingFilter) && $ratingFilter >= 1 && $ratingFilter <= 5) {
            $baseQuery->where('rating', $ratingFilter);
        }

        $reviews = (clone $baseQuery)
            ->latest('created_at')
            ->paginate($perPage)
            ->appends($request->only(['q', 'rating', 'per_page']));

        $statsQuery = clone $baseQuery;

        $summary = [
            'count'      => (clone $statsQuery)->count(),
            'average'    => round((float) (clone $statsQuery)->avg('rating'), 2),
            'five_star'  => (clone $statsQuery)->where('rating', 5)->count(),
            'four_star'  => (clone $statsQuery)->where('rating', 4)->count(),
            'three_star' => (clone $statsQuery)->where('rating', 3)->count(),
            'two_star'   => (clone $statsQuery)->where('rating', 2)->count(),
            'one_star'   => (clone $statsQuery)->where('rating', 1)->count(),
        ];

        return view('seller.reviews.index', [
            'reviews'      => $reviews,
            'summary'      => $summary,
            'search'       => $search,
            'ratingFilter' => $ratingFilter,
            'perPage'      => $perPage,
        ]);
    }

    public function respond(Request $request, Review $review)
    {
        $seller = Auth::user();
        $shop = $seller->shop;

        if (! $shop) {
            abort(403, 'No shop assigned to your account.');
        }

        if ((int) $review->shop_id !== (int) $shop->id) {
            abort(403, 'You are not authorized to respond to this review.');
        }

        $data = $request->validate([
            'seller_response' => ['nullable', 'string', 'max:2000'],
        ]);

        $review->seller_response = $data['seller_response'] ?? null;
        $review->seller_responded_at = $data['seller_response'] ? now() : null;
        $review->save();

        // Email the buyer about the seller's response
        try {
            $order = $review->order()->with('user')->first();
            $buyer = optional($order)->user;
            if ($buyer && !empty($buyer->email) && !empty($review->seller_response)) {
                Mail::to($buyer->email)->send(new BuyerReviewResponseMail($review, $buyer));
            }
        } catch (\Throwable $e) {}

        // Create Activity notification for the buyer (bell icon)
        try {
            $order = $order ?? $review->order; // reuse if already loaded
            $buyerId = (int) optional($order)->user_id;
            if ($buyerId > 0 && !empty($review->seller_response)) {
                // Prefer deep link to buyer's order details if route exists
                $link = route('notifications.index');
                try {
                    if (\Illuminate\Support\Facades\Route::has('buyer.orders.show')) {
                        $link = route('buyer.orders.show', $order->id);
                    }
                } catch (\Throwable $e) {}

                Activity::create([
                    'user_id'      => $buyerId,
                    'is_read'      => false,
                    'type'         => Activity::TYPE_PRODUCT,
                    'related_id'   => $review->id,
                    'related_type' => Review::class,
                    'description'  => 'Seller responded to your review',
                    'link'         => $link,
                    'causer_id'    => (int) $seller->id,
                    'causer_type'  => User::class,
                    'properties'   => [
                        'order_id'     => (int) optional($order)->id,
                        'product_id'   => (int) optional($review->orderItem->product)->id,
                        'shop_id'      => (int) optional($review->shop)->id,
                    ],
                ]);
            }
        } catch (\Throwable $e) {}

        return back()->with('status', 'Response saved.');
    }
}
