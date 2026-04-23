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
            $pages = (int) ceil($count / $this->pageSizePerLocale());

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

        $publicProductsLastmod = $this->latestAtomForQuery($this->productsQuery());

        foreach ($this->supportedLocaleCodes() as $localeCode) {
            $add(localized_route('home', [], true, $localeCode), $this->homepageLastmod(), 'daily', '1.0');
            $add(localized_route('listings', [], true, $localeCode), $publicProductsLastmod, 'daily', '0.9');
        }

        foreach ([
            ['categories.index', 'weekly', '0.7'],
            ['shops.index', 'weekly', '0.7'],
            ['blog.index', 'weekly', '0.5'],
        ] as [$name, $changefreq, $priority]) {
            if (Route::has($name) || route_has_localized_variant($name)) {
                foreach ($this->supportedLocaleCodes() as $localeCode) {
                    $add(localized_route($name, [], true, $localeCode), null, $changefreq, $priority);
                }
            }
        }

        foreach ($this->staticRouteNames() as $name) {
            if (Route::has($name) || route_has_localized_variant($name)) {
                foreach ($this->supportedLocaleCodes() as $localeCode) {
                    $add(localized_route($name, [], true, $localeCode), null, 'yearly', '0.3');
                }
            }
        }

        return $this->xmlView('sitemap', ['urls' => $urls]);
    }

    public function products(int $page)
    {
        $this->abortInvalidPage($this->productsQuery(), $page);

        $urls = $this->productsQuery()
            ->orderBy('id')
            ->forPage($page, $this->pageSizePerLocale())
            ->get(['slug', 'updated_at', 'created_at'])
            ->flatMap(fn (Product $product) => collect($this->supportedLocaleCodes())->map(fn (string $localeCode) => [
                'loc' => localized_route('listing.show', $product->slug, true, $localeCode),
                'lastmod' => $this->atom($product->updated_at ?? $product->created_at),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ]))
            ->all();

        return $this->xmlView('sitemap', ['urls' => $urls]);
    }

    public function categories(int $page)
    {
        $this->abortInvalidPage($this->categoriesQuery(), $page);

        $urls = $this->categoriesQuery()
            ->orderBy('id')
            ->forPage($page, $this->pageSizePerLocale())
            ->get(['slug', 'updated_at', 'created_at'])
            ->flatMap(fn (Category $category) => collect($this->supportedLocaleCodes())->map(fn (string $localeCode) => [
                'loc' => localized_route('category.show', $category->slug, true, $localeCode),
                'lastmod' => $this->atom($category->updated_at ?? $category->created_at),
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ]))
            ->all();

        return $this->xmlView('sitemap', ['urls' => $urls]);
    }

    public function shops(int $page)
    {
        $this->abortInvalidPage($this->shopsQuery(), $page);

        $urls = $this->shopsQuery()
            ->orderBy('id')
            ->forPage($page, $this->pageSizePerLocale())
            ->get(['slug', 'id', 'updated_at', 'created_at'])
            ->flatMap(function (Shop $shop) {
                $routeParam = $shop->slug ?: $shop->id;

                return collect($this->supportedLocaleCodes())->map(fn (string $localeCode) => [
                    'loc' => localized_route('shop.show', $routeParam, true, $localeCode),
                    'lastmod' => $this->atom($shop->updated_at ?? $shop->created_at),
                    'changefreq' => 'weekly',
                    'priority' => '0.6',
                ]);
            })
            ->all();

        return $this->xmlView('sitemap', ['urls' => $urls]);
    }

    public function blog(int $page)
    {
        $this->abortInvalidPage($this->postsQuery(), $page);

        $urls = $this->postsQuery()
            ->orderBy('id')
            ->forPage($page, $this->pageSizePerLocale())
            ->get(['slug', 'published_at', 'updated_at', 'created_at'])
            ->flatMap(fn (BlogPost $post) => collect($this->supportedLocaleCodes())->map(fn (string $localeCode) => [
                'loc' => localized_route('blog.show', $post->slug, true, $localeCode),
                'lastmod' => $this->atom($post->published_at ?? $post->updated_at ?? $post->created_at),
                'changefreq' => 'weekly',
                'priority' => '0.5',
            ]))
            ->all();

        return $this->xmlView('sitemap', ['urls' => $urls]);
    }

    private function productsQuery(): Builder
    {
        return Product::query()
            ->where('is_active', 1)
            ->whereHas('shop', fn (Builder $query) => $query->where('is_active', true))
            ->whereNotNull('slug')
            ->where('slug', '!=', '');
    }

    private function categoriesQuery(): Builder
    {
        return Category::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->where(function (Builder $query) {
                $query->whereHas('products', fn (Builder $productQuery) => $this->activePublicProductFilter($productQuery))
                    ->orWhereHas('childrenRecursive.products', fn (Builder $productQuery) => $this->activePublicProductFilter($productQuery));
            });
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

    private function activePublicProductFilter(Builder $query): Builder
    {
        return $query
            ->where('is_active', 1)
            ->whereHas('shop', fn (Builder $shopQuery) => $shopQuery->where('is_active', true));
    }

    private function homepageLastmod(): ?string
    {
        return collect([
            $this->latestAtomForQuery($this->productsQuery()),
            $this->latestAtomForQuery($this->shopsQuery()),
            $this->latestAtomForQuery($this->postsQuery()),
        ])
            ->filter()
            ->sortDesc()
            ->first();
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
        $lastPage = max(1, (int) ceil($count / $this->pageSizePerLocale()));

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

    /**
     * @return array<int, string>
     */
    private function supportedLocaleCodes(): array
    {
        return array_keys(supported_locales());
    }

    private function pageSizePerLocale(): int
    {
        return max(1, (int) floor(self::PAGE_SIZE / max(1, count($this->supportedLocaleCodes()))));
    }
}
