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
use Illuminate\Support\Facades\Auth;

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

        // Personalized/trending picks
        $featuredProducts = $this->recommendations->trendingForUser(Auth::user(), 8);

        $featuredDigitals = Product::where('is_active', 1)
            ->where('type', 'digital')
            ->latest()
            ->with([
                'media',
                'shop' => function ($q) {
                    $q->withCount('reviews')->withAvg('reviews', 'rating');
                },
            ])
            ->take(8)
            ->get();

        // Latest service offerings for inspiration
        $services = Product::where('is_active', 1)
            ->where('type', 'service')
            ->latest()
            ->with([
                'media',
                'shop' => function ($q) {
                    $q->withCount('reviews')->withAvg('reviews', 'rating');
                },
            ])
            ->take(8)
            ->get();

        // Top sellers (shops with completed/delivered orders)
        $shops = Shop::withCount([
                'orders as completed_orders_count' => function ($q) {
                    $q->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED]);
                },
            ])
            ->whereHas('orders', function ($q) {
                $q->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERED]);
            })
            ->orderByDesc('completed_orders_count')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        // If no shops have completed/delivered orders yet, fallback to latest shops
        if ($shops->isEmpty()) {
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
            'featuredDigitals'  => $featuredDigitals,
            'services'          => $services,
            'shops'             => $shops,
            'activeDeals'       => $activeDeals,
            'heroSlides'        => $heroSlides,
        ]);
    }
}
