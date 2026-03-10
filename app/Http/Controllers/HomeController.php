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
    private const HOME_ROTATOR_PAGE_SIZE = 8;
    private const HOME_ROTATOR_PAGES_PER_LOAD = 3;

    protected ProductRecommendationService $recommendations;
    private array $homeSectionOffsets = [];

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
        $windowSize = self::HOME_ROTATOR_PAGE_SIZE * self::HOME_ROTATOR_PAGES_PER_LOAD;

        // Top-level categories (no parent)
        $categories = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        // Keep the homepage payload small: show a rotating 24-item window per
        // section and advance that window across visits so additional listings
        // appear without rendering the full marketplace on every page load.
        $featuredProducts = $this->homeSectionWindow('all', null, $windowSize, 0, $listingCacheTtl);
        $justForYouProducts = $this->homeSectionWindow('all', null, $windowSize, self::HOME_ROTATOR_PAGE_SIZE, $listingCacheTtl);
        $featuredDigitals = $this->homeSectionWindow('digital', 'digital', $windowSize, 0, $listingCacheTtl);
        $services = $this->homeSectionWindow('service', 'service', $windowSize, 0, $listingCacheTtl);

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

    private function homeSectionWindow(string $scope, ?string $type, int $limit, int $extraOffset = 0, int $cacheMinutes = 10): Collection
    {
        $ids = $this->cachedMixedListingIds($scope, $type, $cacheMinutes);
        if (empty($ids)) {
            return collect();
        }

        $total = count($ids);
        $start = ($this->baseSectionOffset($scope, $total, $limit) + $extraOffset) % $total;
        $windowIds = $this->sliceRotatingIds($ids, $start, $limit);

        return $this->hydrateListings($windowIds, $type);
    }

    private function cachedMixedListingIds(string $scope, ?string $type = null, int $cacheMinutes = 10): array
    {
        $cacheKey = 'home:mixed-listing-ids:' . $scope . ':v2';

        $ids = Cache::remember($cacheKey, now()->addMinutes($cacheMinutes), function () use ($type) {
            return $this->buildMixedListingIds($type);
        });

        return is_array($ids) ? $ids : [];
    }

    private function hydrateListings(array $ids, ?string $type = null): Collection
    {
        if (empty($ids)) {
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
            $query->whereDisplayType($type);
        }

        $productsById = $query->get()->keyBy('id');

        return collect($ids)
            ->map(function ($id) use ($productsById) {
                return $productsById->get((int) $id);
            })
            ->filter()
            ->values();
    }

    private function baseSectionOffset(string $scope, int $total, int $step): int
    {
        if ($total < 1) {
            return 0;
        }

        if (array_key_exists($scope, $this->homeSectionOffsets)) {
            return $this->homeSectionOffsets[$scope];
        }

        $session = request()->session();
        $current = (int) $session->get("home_section_offsets.$scope", 0);
        $normalized = $current % $total;
        $next = ($normalized + max(1, $step)) % $total;

        $session->put("home_section_offsets.$scope", $next);
        $this->homeSectionOffsets[$scope] = $normalized;

        return $normalized;
    }

    private function sliceRotatingIds(array $ids, int $offset, int $limit): array
    {
        $count = count($ids);
        if ($count < 1 || $limit < 1 || $count <= $limit) {
            return array_values($ids);
        }

        $offset = $offset % $count;
        $slice = array_slice($ids, $offset, $limit);

        if (count($slice) < $limit) {
            $slice = array_merge($slice, array_slice($ids, 0, $limit - count($slice)));
        }

        return array_values($slice);
    }

    private function buildMixedListingIds(?string $type = null): array
    {
        $query = Product::query()
            ->where('is_active', 1);

        if (!empty($type)) {
            $query->whereDisplayType($type);
        }

        $products = $query
            ->select(['id', 'category_id'])
            ->latest('id')
            ->get();

        if ($products->isEmpty()) {
            return [];
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
