<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Support\Facades\Route;

class SitemapController extends Controller
{
    /**
     * Generate a lightweight XML sitemap for public pages.
     */
    public function __invoke()
    {
        $urls = [];

        $add = function (string $loc, ?string $lastmod = null, string $changefreq = 'weekly', string $priority = '0.6') use (&$urls) {
            $urls[] = [
                'loc' => $loc,
                'lastmod' => $lastmod,
                'changefreq' => $changefreq,
                'priority' => $priority,
            ];
        };

        $add(url('/'), now()->toAtomString(), 'daily', '1.0');
        $add(route('listings'), now()->toAtomString(), 'daily', '0.9');

        if (Route::has('categories.index')) {
            $add(route('categories.index'), now()->toAtomString(), 'weekly', '0.7');
        }

        if (Route::has('shops.index')) {
            $add(route('shops.index'), now()->toAtomString(), 'weekly', '0.7');
        }

        if (Route::has('blog.index')) {
            $add(route('blog.index'), null, 'weekly', '0.5');
        }

        $staticRoutes = [
            'about',
            'privacy',
            'terms',
            'become-seller',
            'seller-forum',
            'seller-tips',
            'buyer-tips',
            'buyer-terms',
            'house-policy',
            'user-agreement',
            'privacy.policy',
            'terms.of.service',
            'intro',
            'restricted_for_sale',
            'cetsyip_policy',
            'payment_policy',
        ];

        foreach ($staticRoutes as $name) {
            if (Route::has($name)) {
                $add(route($name), null, 'yearly', '0.3');
            }
        }

        $products = Product::query()
            ->where('is_active', 1)
            ->orderByDesc('updated_at')
            ->limit(500)
            ->get(['slug', 'updated_at', 'created_at']);

        foreach ($products as $product) {
            if (!$product->slug) {
                continue;
            }

            $add(
                route('listing.show', $product->slug),
                optional($product->updated_at ?? $product->created_at)->toAtomString(),
                'daily',
                '0.8'
            );
        }

        $categories = Category::query()
            ->orderByDesc('updated_at')
            ->limit(200)
            ->get(['slug', 'updated_at', 'created_at']);

        foreach ($categories as $category) {
            if (!$category->slug) {
                continue;
            }

            $add(
                route('category.show', $category->slug),
                optional($category->updated_at ?? $category->created_at)->toAtomString(),
                'weekly',
                '0.6'
            );
        }

        $shops = Shop::query()
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->limit(200)
            ->get(['slug', 'id', 'updated_at', 'created_at']);

        foreach ($shops as $shop) {
            $slug = $shop->slug ?: $shop->id;

            $add(
                route('shop.show', $slug),
                optional($shop->updated_at ?? $shop->created_at)->toAtomString(),
                'weekly',
                '0.6'
            );
        }

        $posts = BlogPost::query()
            ->live()
            ->orderByDesc('published_at')
            ->limit(200)
            ->get(['slug', 'published_at', 'updated_at', 'created_at']);

        foreach ($posts as $post) {
            if (!$post->slug) {
                continue;
            }

            $lastmod = $post->published_at ?? $post->updated_at ?? $post->created_at;

            $add(
                route('blog.show', $post->slug),
                optional($lastmod)->toAtomString(),
                'weekly',
                '0.5'
            );
        }

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
