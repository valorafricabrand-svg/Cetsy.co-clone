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
        // Top-level categories (no parent)
        $categories = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        // Randomized mixed-category pool so homepage sections can rotate through
        // current listings until richer real-world activity data is available.
        $allListingsPool = $this->randomMixedListings(0);
        $featuredProducts = $allListingsPool->shuffle()->values();
        $justForYouProducts = $allListingsPool->shuffle()->values();

        $featuredDigitals = $this->randomMixedListings(64, 'digital');

        // Services pool for rotating "Most Trending Services" (all active services)
        $services = $this->randomMixedListings(0, 'service');

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

    private function randomMixedListings(int $limit = 96, ?string $type = null): Collection
    {
        $query = Product::query()
            ->where('is_active', 1)
            ->with([
                'media',
                'shop' => function ($q) {
                    $q->withCount('reviews')->withAvg('reviews', 'rating');
                },
            ]);

        if (!empty($type)) {
            $query->where('type', $type);
        }

        $products = $query
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

        $targetLimit = $limit > 0 ? $limit : $products->count();
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

        return $mixed->unique('id')->values();
    }
}
