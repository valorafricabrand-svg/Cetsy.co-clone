<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Deal;
use App\Models\HeroSlide;
use App\Models\Order;
use App\Services\Recommendation\ProductRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    protected ProductRecommendationService $recommendations;

    public function __construct(ProductRecommendationService $recommendations)
    {
        $this->recommendations = $recommendations;
    }

    /**
     * Display the homepage with top-level categories and latest products.
     */
    public function index()
    {
        $listingCacheTtl = $this->homeListingsCacheTtlMinutes();

        // Top-level categories (no parent)
        $categories = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        // Randomized mixed-category pool so homepage sections can rotate through
        // current listings until richer real-world activity data is available.
        $allListingsPool = $this->cachedMixedListings('all', 0, null, $listingCacheTtl);
        $featuredProducts = $allListingsPool->values();
        $justForYouProducts = $this->rotateCollection($allListingsPool, 8);

        $featuredDigitals = $this->cachedMixedListings('digital', 0, 'digital', $listingCacheTtl);

        // Services pool for rotating "Most Trending Services" (all active services)
        $services = $this->cachedMixedListings('service', 0, 'service', $listingCacheTtl);

        // Top sellers (shops with completed/delivered orders)
        $topShopCounts = Order::select('shop_id', DB::raw('COUNT(*) as completed_orders_count'))
            ->whereNotNull('shop_id')
            ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED])
            ->groupBy('shop_id')
            ->orderByDesc('completed_orders_count')
            ->take(8)
            ->get();

        $shopIdOrder = $topShopCounts->pluck('shop_id')->filter()->all();
        if (!empty($shopIdOrder)) {
            $countsMap = $topShopCounts->pluck('completed_orders_count', 'shop_id');
            $shops = Shop::whereIn('id', $shopIdOrder)->get()
                ->sortBy(function ($shop) use ($shopIdOrder) {
                    return array_search($shop->id, $shopIdOrder);
                })
                ->values();
            // attach computed counts
            $shops->each(function ($shop) use ($countsMap) {
                $shop->completed_orders_count = $countsMap[$shop->id] ?? 0;
            });
        } else {
            // Fallback: latest shops if no completed/delivered orders yet
            $shops = Shop::latest()->take(8)->get();
        }

        // Highlight a few active deals (for homepage strip)
        $activeDeals = Deal::active()
            ->withCount('products')
            ->orderByDesc('discount_percent')
            ->orderBy('starts_at')
            ->take(3)
            ->get();

        // Homepage hero slides (admin-managed)
        $heroSlides = HeroSlide::active()
            ->with([
                'deal:id,name,discount_percent',
                'category:id,name,slug',
            ])
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        return themed_view('index', [
            'categories'        => $categories,
            'featuredProducts'  => $featuredProducts,
            'justForYouProducts'=> $justForYouProducts,
            'featuredDigitals'  => $featuredDigitals,
            'services'          => $services,
            'shops'             => $shops,
            'activeDeals'       => $activeDeals,
            'heroSlides'        => $heroSlides,
        ]);
    }

    private function cachedMixedListings(string $scope, int $limit = 0, ?string $type = null, int $cacheMinutes = 10): Collection
    {
        $cacheKey = 'home:mixed-listing-ids:' . $scope . ':v1';
        $ids = Cache::remember($cacheKey, now()->addMinutes($cacheMinutes), function () use ($type) {
            return $this->buildMixedListingIds($type);
        });

        if (empty($ids) || !is_array($ids)) {
            return collect();
        }

        $query = Product::query()
            ->where('is_active', 1)
            ->whereIn('id', $ids)
            ->with([
                'media',
                'shop' => function ($q) {
                    $q->withCount('reviews')->withAvg('reviews', 'rating');
                },
            ]);

        if (!empty($type)) {
            $query->where('type', $type);
        }

        $productsById = $query->get()->keyBy('id');
        $ordered = collect($ids)
            ->map(function ($id) use ($productsById) {
                return $productsById->get((int) $id);
            })
            ->filter()
            ->values();

        if ($limit > 0) {
            $ordered = $ordered->take($limit)->values();
        }

        return $ordered;
    }

    private function buildMixedListingIds(?string $type = null): array
    {
        $query = Product::query()
            ->where('is_active', 1);

        if (!empty($type)) {
            $query->where('type', $type);
        }

        $products = $query
            ->select(['id', 'category_id'])
            ->latest('id')
            ->get();

        if ($products->isEmpty()) {
            return collect();
        }

        $buckets = $products
            ->shuffle()
            ->groupBy(function ($product) {
                return (int) ($product->category_id ?? 0);
            })
            ->map(function ($group) {
                return $group->shuffle()->values();
            })
            ->values();

        $targetLimit = $products->count();
        $mixed = collect();
        while ($mixed->count() < $targetLimit) {
            $added = false;

            foreach ($buckets as $idx => $bucket) {
                if ($bucket->isEmpty()) {
                    continue;
                }

                $mixed->push($bucket->shift());
                $buckets[$idx] = $bucket;
                $added = true;

                if ($mixed->count() >= $targetLimit) {
                    break;
                }
            }

            if (!$added) {
                break;
            }
        }

        return $mixed
            ->pluck('id')
            ->unique()
            ->values()
            ->all();
    }

    private function rotateCollection(Collection $items, int $offset): Collection
    {
        $count = $items->count();
        if ($count < 2) {
            return $items->values();
        }

        $normalized = $offset % $count;
        if ($normalized === 0) {
            $normalized = 1;
        }

        return $items
            ->slice($normalized)
            ->concat($items->take($normalized))
            ->values();
    }

    private function homeListingsCacheTtlMinutes(): int
    {
        $raw = 10;
        try {
            $raw = (int) (function_exists('setting')
                ? setting('home_listings_cache_ttl_minutes', 10)
                : 10);
        } catch (\Throwable $e) {
            $raw = 10;
        }

        return max(1, min(1440, $raw));
    }
}
