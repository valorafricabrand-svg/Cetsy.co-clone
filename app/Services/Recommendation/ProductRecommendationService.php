<?php

namespace App\Services\Recommendation;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\User;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductRecommendationService
{
    public function trendingForUser(
        ?User $user = null,
        int $limit = 8,
        ?Carbon $since = null,
        array $excludeProductIds = [],
        ?Product $contextProduct = null
    ): Collection {
        $limit = max($limit, 1);
        $since = $since ?: Carbon::now()->subDays(30);

        if ($contextProduct) {
            $excludeProductIds[] = $contextProduct->id;
        }

        $excludeProductIds = array_values(array_unique(array_filter($excludeProductIds)));

        $preferences = [
            'categories'    => [],
            'tags'          => [],
            'shops'         => [],
            'purchased_ids' => [],
        ];

        $sellerShopId = optional($user?->shop)->id;

        if ($user) {
            $preferences = $this->gatherUserPreferences($user, $since);
            if (!empty($preferences['purchased_ids'])) {
                $excludeProductIds = array_values(array_unique(array_merge(
                    $excludeProductIds,
                    $preferences['purchased_ids']
                )));
            }
        }

        $baseQuery = $this->baseTrendingQuery($since);

        if (!empty($excludeProductIds)) {
            $baseQuery->whereNotIn('products.id', $excludeProductIds);
        }

        if ($sellerShopId) {
            $baseQuery->where(function (Builder $query) use ($sellerShopId) {
                $query->whereNull('products.shop_id')
                    ->orWhere('products.shop_id', '<>', $sellerShopId);
            });
        }

        $candidates = $baseQuery
            ->limit(max($limit * 4, 32))
            ->get();

        if ($candidates->isEmpty()) {
            return $this->fallbackProducts($limit, $excludeProductIds, $sellerShopId);
        }

        $categoryAffinity = $preferences['categories'] ?? [];
        $tagAffinity       = $preferences['tags'] ?? [];
        $shopAffinity      = $preferences['shops'] ?? [];

        $contextTags     = $contextProduct ? $this->explodeTags($contextProduct->tags) : [];
        $contextCategory = $contextProduct?->category_id;

        $ranked = $candidates
            ->map(function (Product $product) use (
                $categoryAffinity,
                $tagAffinity,
                $shopAffinity,
                $contextTags,
                $contextCategory
            ) {
                $score = (float) ($product->score ?? 0);

                if ($product->category_id && isset($categoryAffinity[$product->category_id])) {
                    $score += $categoryAffinity[$product->category_id];
                }

                if ($product->shop_id && isset($shopAffinity[$product->shop_id])) {
                    $score += $shopAffinity[$product->shop_id];
                }

                $productTags = $this->explodeTags($product->tags);
                foreach ($productTags as $tag) {
                    if (isset($tagAffinity[$tag])) {
                        $score += $tagAffinity[$tag];
                    }
                }

                if ($contextCategory && $product->category_id === $contextCategory) {
                    $score += 6.0;
                }

                if (!empty($contextTags)) {
                    $overlap = count(array_intersect($contextTags, $productTags));
                    if ($overlap > 0) {
                        $score += $overlap * 2.5;
                    }
                }

                $product->relevance_score = $score;

                return $product;
            })
            ->sortByDesc(fn (Product $product) => $product->relevance_score)
            ->unique('id')
            ->values();

        $selected = $ranked->take($limit);

        if ($selected->count() < $limit) {
            $needed = $limit - $selected->count();
            $selected = $selected->concat(
                $this->fallbackProducts(
                    $needed,
                    array_merge($excludeProductIds, $selected->pluck('id')->all()),
                    $sellerShopId
                )
            );
        }

        return $selected->values();
    }

    public function relatedToProduct(
        Product $product,
        ?User $user = null,
        int $limit = 8,
        ?Carbon $since = null
    ): Collection {
        $since = $since ?: Carbon::now()->subDays(90);

        $coPurchase = $this->normalizeScores(
            $this->coPurchaseScores($product, $since),
            12.0
        );
        $coBrowse = $this->normalizeScores(
            $this->coBrowseScores($product, $since),
            6.0
        );

        $base = $this->trendingForUser(
            $user,
            max($limit * 3, 24),
            $since,
            [$product->id],
            $product
        );

        $additionalIds = array_diff(
            array_unique(array_merge(array_keys($coPurchase), array_keys($coBrowse))),
            $base->pluck('id')->all()
        );

        if (!empty($additionalIds)) {
            $additional = Product::query()
                ->with('media')
                ->whereIn('id', $additionalIds)
                ->where('is_active', 1)
                ->get();

            foreach ($additional as $extra) {
                $extra->relevance_score = 0.0;
            }

            $base = $base->concat($additional);
        }

        $shopExclusion = optional($user?->shop)->id;

        $ranked = $base
            ->map(function (Product $candidate) use ($coPurchase, $coBrowse, $product, $shopExclusion) {
                $score = (float) ($candidate->relevance_score ?? 0.0);

                if (isset($coPurchase[$candidate->id])) {
                    $score += $coPurchase[$candidate->id];
                }

                if (isset($coBrowse[$candidate->id])) {
                    $score += $coBrowse[$candidate->id];
                }

                if ($candidate->shop_id === $product->shop_id) {
                    $score -= 3.0; // de-prioritize identical shop listings
                }

                if ($shopExclusion && $candidate->shop_id === $shopExclusion) {
                    $score -= 2.0;
                }

                $candidate->relevance_score = $score;

                return $candidate;
            })
            ->sortByDesc(fn (Product $p) => $p->relevance_score)
            ->unique('id')
            ->take($limit)
            ->values();

        if ($ranked->count() < $limit) {
            $ranked = $ranked->concat(
                $this->fallbackProducts(
                    $limit - $ranked->count(),
                    array_merge([$product->id], $ranked->pluck('id')->all()),
                    $product->shop_id
                )
            )->unique('id')->take($limit)->values();
        }

        return $ranked;
    }

    protected function baseTrendingQuery(Carbon $since): Builder
    {
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

        return Product::query()
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
    }

    protected function gatherUserPreferences(User $user, Carbon $since): array
    {
        $windowStart = $since->copy()->subDays(180);

        $orders = Order::query()
            ->select([
                'products.id as product_id',
                'products.category_id',
                'products.shop_id',
                'products.tags',
            ])
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.user_id', $user->id)
            ->where('orders.created_at', '>=', $windowStart)
            ->get();

        $wishlistProducts = Wishlist::query()
            ->where('user_id', $user->id)
            ->with('product:id,category_id,shop_id,tags')
            ->get()
            ->pluck('product')
            ->filter();

        $viewedProducts = ProductView::query()
            ->join('products', 'product_views.product_id', '=', 'products.id')
            ->where('product_views.viewer_id', $user->id)
            ->where('product_views.created_at', '>=', $windowStart)
            ->get(['products.category_id', 'products.tags']);

        $categoryScores = [];
        $tagScores = [];
        $shopScores = [];
        $purchasedIds = [];

        foreach ($orders as $row) {
            $purchasedIds[] = $row->product_id;
            if ($row->category_id) {
                $categoryScores[$row->category_id] = ($categoryScores[$row->category_id] ?? 0) + 6;
            }
            if ($row->shop_id) {
                $shopScores[$row->shop_id] = ($shopScores[$row->shop_id] ?? 0) + 2;
            }
            foreach ($this->explodeTags($row->tags) as $tag) {
                $tagScores[$tag] = ($tagScores[$tag] ?? 0) + 2.5;
            }
        }

        foreach ($wishlistProducts as $product) {
            if (!$product) {
                continue;
            }
            if ($product->category_id) {
                $categoryScores[$product->category_id] = ($categoryScores[$product->category_id] ?? 0) + 3;
            }
            if ($product->shop_id) {
                $shopScores[$product->shop_id] = ($shopScores[$product->shop_id] ?? 0) + 1.5;
            }
            foreach ($this->explodeTags($product->tags) as $tag) {
                $tagScores[$tag] = ($tagScores[$tag] ?? 0) + 1.5;
            }
        }

        foreach ($viewedProducts as $viewed) {
            if ($viewed->category_id) {
                $categoryScores[$viewed->category_id] = ($categoryScores[$viewed->category_id] ?? 0) + 1;
            }
            foreach ($this->explodeTags($viewed->tags) as $tag) {
                $tagScores[$tag] = ($tagScores[$tag] ?? 0) + 0.5;
            }
        }

        return [
            'categories'    => $categoryScores,
            'tags'          => $tagScores,
            'shops'         => $shopScores,
            'purchased_ids' => array_values(array_unique($purchasedIds)),
        ];
    }

    protected function explodeTags(?string $tags): array
    {
        if (empty($tags)) {
            return [];
        }

        return collect(preg_split('/[,;\n\r]+/', strtolower($tags)))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->values()
            ->all();
    }

    protected function fallbackProducts(int $limit, array $excludeProductIds = [], ?int $excludeShopId = null): Collection
    {
        return Product::query()
            ->with('media')
            ->where('is_active', 1)
            ->when(!empty($excludeProductIds), fn ($q) => $q->whereNotIn('id', $excludeProductIds))
            ->when($excludeShopId, function ($q) use ($excludeShopId) {
                $q->where(function ($inner) use ($excludeShopId) {
                    $inner->whereNull('shop_id')
                        ->orWhere('shop_id', '<>', $excludeShopId);
                });
            })
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Normalize scores into the given weight range.
     */
    protected function normalizeScores(array $scores, float $weight): array
    {
        if (empty($scores)) {
            return [];
        }

        $max = max($scores);
        if ($max <= 0) {
            return [];
        }

        return array_map(function ($value) use ($max, $weight) {
            return ($value / $max) * $weight;
        }, $scores);
    }

    protected function coPurchaseScores(Product $product, Carbon $since): array
    {
        return DB::table('order_items as seed')
            ->join('orders', 'orders.id', '=', 'seed.order_id')
            ->join('order_items as related', 'related.order_id', '=', 'orders.id')
            ->where('seed.product_id', $product->id)
            ->where('related.product_id', '!=', $product->id)
            ->where('orders.created_at', '>=', $since)
            ->selectRaw('related.product_id as product_id, SUM(related.quantity) as weight')
            ->groupBy('related.product_id')
            ->orderByDesc('weight')
            ->limit(100)
            ->pluck('weight', 'product_id')
            ->toArray();
    }

    protected function coBrowseScores(Product $product, Carbon $since): array
    {
        return DB::table('product_views as seed')
            ->join('product_views as related', function ($join) {
                $join->on('seed.viewer_id', '=', 'related.viewer_id')
                    ->whereNotNull('seed.viewer_id')
                    ->whereNotNull('related.viewer_id');
            })
            ->where('seed.product_id', $product->id)
            ->where('related.product_id', '!=', $product->id)
            ->where('seed.created_at', '>=', $since)
            ->where('related.created_at', '>=', $since)
            ->selectRaw('related.product_id as product_id, COUNT(*) as weight')
            ->groupBy('related.product_id')
            ->orderByDesc('weight')
            ->limit(100)
            ->pluck('weight', 'product_id')
            ->toArray();
    }
}