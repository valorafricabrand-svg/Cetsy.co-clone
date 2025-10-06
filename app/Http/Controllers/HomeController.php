<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
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

        // Discover new shops
        $shops = Shop::latest()
            ->take(8)
            ->get();

        return themed_view('index', [
            'categories'        => $categories,
            'featuredProducts'  => $featuredProducts,
            'featuredDigitals'  => $featuredDigitals,
            'services'          => $services,
            'shops'             => $shops,
        ]);
    }
}
