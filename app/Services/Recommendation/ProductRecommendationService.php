<?php

namespace App\Services\Recommendation;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductRecommendationService
{
    /**
     * Build a trending set of products for a buyer.
     */
    public function trendingForUser(?User $user = null, int $limit = 8, ?Carbon $since = null): Collection
    {
        $since = $since ?: Carbon::now()->subDays(30);

        $paidStatuses = [
            Order::STATUS_PROCESSING,
            Order::STATUS_SHIPPED,
            Order::STATUS_DELIVERED,
            Order::STATUS_COMPLETED,
        ];

        $salesSub = Order::query()
            ->select([
                'order_items.product_id',
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('SUM(order_items.quantity * order_items.price) as revenue'),
            ])
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.status', $paidStatuses)
            ->where('orders.created_at', '>=', $since)
            ->groupBy('order_items.product_id');

        $viewsSub = ProductView::query()
            ->select([
                'product_id',
                DB::raw('COUNT(*) as views'),
            ])
            ->where('created_at', '>=', $since)
            ->groupBy('product_id');

        $purchasedProductIds = collect();
        $sellerShopId = null;

        if ($user) {
            $purchasedProductIds = Order::query()
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.user_id', $user->id)
                ->pluck('order_items.product_id')
                ->unique();

            $sellerShopId = optional($user->shop)->id;
        }

        $query = Product::query()
            ->with('media')
            ->select([
                'products.*',
                DB::raw('COALESCE(sales.units_sold, 0) as units_sold'),
                DB::raw('COALESCE(sales.revenue, 0) as revenue'),
                DB::raw('COALESCE(views.views, 0) as views'),
                DB::raw('(COALESCE(sales.revenue, 0) * 0.7 + COALESCE(views.views, 0) * 0.3) as score'),
            ])
            ->leftJoinSub($salesSub, 'sales', 'sales.product_id', '=', 'products.id')
            ->leftJoinSub($viewsSub, 'views', 'views.product_id', '=', 'products.id')
            ->where('products.is_active', 1);

        if ($purchasedProductIds->isNotEmpty()) {
            $query->whereNotIn('products.id', $purchasedProductIds->all());
        }

        if ($sellerShopId) {
            $query->where(function ($inner) use ($sellerShopId) {
                $inner->whereNull('products.shop_id')
                    ->orWhere('products.shop_id', '<>', $sellerShopId);
            });
        }

        $products = $query
            ->orderByDesc('score')
            ->orderByDesc('revenue')
            ->orderByDesc('views')
            ->limit($limit)
            ->get();

        if ($products->count() >= $limit) {
            return $products->take($limit);
        }

        $remaining = $limit - $products->count();

        $fallback = Product::query()
            ->with('media')
            ->where('is_active', 1)
            ->when($purchasedProductIds->isNotEmpty(), function ($q) use ($purchasedProductIds) {
                $q->whereNotIn('id', $purchasedProductIds->all());
            })
            ->when($sellerShopId, function ($q) use ($sellerShopId) {
                $q->where(function ($inner) use ($sellerShopId) {
                    $inner->whereNull('shop_id')
                        ->orWhere('shop_id', '<>', $sellerShopId);
                });
            })
            ->whereNotIn('id', $products->pluck('id')->all())
            ->latest()
            ->limit($remaining)
            ->get();

        return $products->concat($fallback);
    }
}
