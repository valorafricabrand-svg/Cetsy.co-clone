<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                // Load the related order without forcing non-existent columns
                'order',
                'orderItem.product' => function ($query) {
                    $query->select('id', 'name', 'type', 'slug');
                },
                'user:id,name',
            ])
            ->where('shop_id', $shop->id)
            ->whereHas('order', function ($query) {
                $query->whereIn('status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED]);
            });

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
                        // Search by order id (no order_number column in schema)
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
}
