<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

class SitemapController extends Controller
{
    private const PAGE_SIZE = 1000;

    public function index()
    {
        $sitemaps = [
            [
                'loc' => route('sitemap.static'),
                'lastmod' => null,
            ],
        ];

        foreach ([
            'products' => $this->productsQuery(),
            'categories' => $this->categoriesQuery(),
            'shops' => $this->shopsQuery(),
            'blog' => $this->postsQuery(),
        ] as $section => $query) {
            $count = (clone $query)->count();
            if ($count < 1) {
                continue;
            }

            $lastmod = $this->latestAtomForQuery(clone $query);
            $pages = (int) ceil($count / self::PAGE_SIZE);

            for ($page = 1; $page <= $pages; $page++) {
                $sitemaps[] = [
                    'loc' => route('sitemap.' . $section, ['page' => $page]),
                    'lastmod' => $lastmod,
                ];
            }
        }

        return $this->xmlView('sitemap-index', ['sitemaps' => $sitemaps]);
    }

    public function static()
    {
        $urls = [];

        $add = function (string $loc, ?string $lastmod = null, string $changefreq = 'weekly', string $priority = '0.6') use (&$urls) {
            $urls[] = compact('loc', 'lastmod', 'changefreq', 'priority');
        };

        $add(url('/'), now()->toAtomString(), 'daily', '1.0');
        $add(route('listings'), now()->toAtomString(), 'daily', '0.9');

        foreach ([
            ['categories.index', 'weekly', '0.7'],
            ['shops.index', 'weekly', '0.7'],
            ['blog.index', 'weekly', '0.5'],
        ] as [$name, $changefreq, $priority]) {
            if (Route::has($name)) {
                $add(route($name), null, $changefreq, $priority);
            }
        }

        foreach ($this->staticRouteNames() as $name) {
            if (Route::has($name)) {
                $add(route($name), null, 'yearly', '0.3');
            }
        }

        return $this->xmlView('sitemap', ['urls' => $urls]);
    }

    public function products(int $page)
    {
        $this->abortInvalidPage($this->productsQuery(), $page);

        $urls = $this->productsQuery()
            ->orderBy('id')
            ->forPage($page, self::PAGE_SIZE)
            ->get(['slug', 'updated_at', 'created_at'])
            ->map(fn (Product $product) => [
                'loc' => route('listing.show', $product->slug),
                'lastmod' => $this->atom($product->updated_at ?? $product->created_at),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ])
            ->all();

        return $this->xmlView('sitemap', ['urls' => $urls]);
    }

    public function categories(int $page)
    {
        $this->abortInvalidPage($this->categoriesQuery(), $page);

        $urls = $this->categoriesQuery()
            ->orderBy('id')
            ->forPage($page, self::PAGE_SIZE)
            ->get(['slug', 'updated_at', 'created_at'])
            ->map(fn (Category $category) => [
                'loc' => route('category.show', $category->slug),
                'lastmod' => $this->atom($category->updated_at ?? $category->created_at),
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ])
            ->all();

        return $this->xmlView('sitemap', ['urls' => $urls]);
    }

    public function shops(int $page)
    {
        $this->abortInvalidPage($this->shopsQuery(), $page);

        $urls = $this->shopsQuery()
            ->orderBy('id')
            ->forPage($page, self::PAGE_SIZE)
            ->get(['slug', 'id', 'updated_at', 'created_at'])
            ->map(function (Shop $shop) {
                $routeParam = $shop->slug ?: $shop->id;

                return [
                    'loc' => route('shop.show', $routeParam),
                    'lastmod' => $this->atom($shop->updated_at ?? $shop->created_at),
                    'changefreq' => 'weekly',
                    'priority' => '0.6',
                ];
            })
            ->all();

        return $this->xmlView('sitemap', ['urls' => $urls]);
    }

    public function blog(int $page)
    {
        $this->abortInvalidPage($this->postsQuery(), $page);

        $urls = $this->postsQuery()
            ->orderBy('id')
            ->forPage($page, self::PAGE_SIZE)
            ->get(['slug', 'published_at', 'updated_at', 'created_at'])
            ->map(fn (BlogPost $post) => [
                'loc' => route('blog.show', $post->slug),
                'lastmod' => $this->atom($post->published_at ?? $post->updated_at ?? $post->created_at),
                'changefreq' => 'weekly',
                'priority' => '0.5',
            ])
            ->all();

        return $this->xmlView('sitemap', ['urls' => $urls]);
    }

    private function productsQuery(): Builder
    {
        return Product::query()
            ->where('is_active', 1)
            ->whereNotNull('slug')
            ->where('slug', '!=', '');
    }

    private function categoriesQuery(): Builder
    {
        return Category::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '');
    }

    private function shopsQuery(): Builder
    {
        return Shop::query()
            ->where('is_active', true);
    }

    private function postsQuery(): Builder
    {
        return BlogPost::query()
            ->live()
            ->whereNotNull('slug')
            ->where('slug', '!=', '');
    }

    private function staticRouteNames(): array
    {
        return [
            'about',
            'privacy',
            'terms',
            'refunds-returns',
            'shipping-delivery',
            'become-seller',
            'seller-policy',
            'seller-forum',
            'seller-tips',
            'prohibited-items',
            'contact',
            'buyer-tips',
            'buyer-terms',
            'house-policy',
            'user-agreement',
            'restricted_for_sale',
            'cetsyip_policy',
            'payment_policy',
        ];
    }

    private function abortInvalidPage(Builder $query, int $page): void
    {
        $count = (clone $query)->count();
        $lastPage = max(1, (int) ceil($count / self::PAGE_SIZE));

        abort_if($page < 1 || $page > $lastPage, 404);
    }

    private function latestAtomForQuery(Builder $query): ?string
    {
        return $this->atom(
            (clone $query)->max('updated_at')
                ?: (clone $query)->max('created_at')
        );
    }

    private function atom($date): ?string
    {
        if (!$date) {
            return null;
        }

        try {
            return $date instanceof Carbon
                ? $date->toAtomString()
                : Carbon::parse($date)->toAtomString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function xmlView(string $view, array $data)
    {
        return response()
            ->view($view, $data)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
