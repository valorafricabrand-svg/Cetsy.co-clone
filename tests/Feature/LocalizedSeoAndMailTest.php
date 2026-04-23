<?php

namespace Tests\Feature;

use App\Mail\CounterOfferMail;
use App\Mail\WelcomeBuyerMail;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Country;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizedSeoAndMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_localized_listing_page_outputs_locale_specific_canonical_and_hreflang_tags(): void
    {
        [, , $product] = $this->createLocalizedMarketplaceFixtures();

        $response = $this->get(route('localized.listing.show', [
            'locale' => 'sw',
            'slug' => $product->slug,
        ]));

        $response
            ->assertOk()
            ->assertSee('Kikombe cha Kiswahili')
            ->assertSee(route('localized.listing.show', ['locale' => 'sw', 'slug' => $product->slug]), false)
            ->assertSee(route('localized.listing.show', ['locale' => 'en', 'slug' => $product->slug]), false)
            ->assertSee('hreflang="sw"', false)
            ->assertSee('hreflang="en"', false)
            ->assertSee('hreflang="x-default"', false);
    }

    public function test_locale_prefixed_shop_route_renders_the_public_shop_page(): void
    {
        [, , $product] = $this->createLocalizedMarketplaceFixtures();
        $shop = $product->shop()->firstOrFail();

        $this->get(route('localized.shop.show', [
            'locale' => 'sw',
            'id' => $shop->slug,
        ]))
            ->assertOk()
            ->assertSee('Duka la Kiswahili')
            ->assertSee('Kikombe cha Kiswahili');
    }

    public function test_locale_prefixed_category_route_renders_the_category_page(): void
    {
        [, , $product] = $this->createLocalizedMarketplaceFixtures();

        $category = Category::create([
            'name' => 'Wall Art',
            'slug' => 'wall-art',
            'listing_type' => 'products',
            'description' => 'Decorative wall art.',
            'listing_fee' => 0.25,
            'listing_frequency' => 4,
        ]);

        $product->update(['category_id' => $category->id]);

        $this->get(route('localized.category.show', [
            'locale' => 'sw',
            'slug' => $category->slug,
        ]))
            ->assertOk()
            ->assertSee('Kikombe cha Kiswahili');
    }

    public function test_locale_prefixed_blog_post_route_renders_the_blog_post_page(): void
    {
        $author = User::factory()->create();
        $category = BlogCategory::create([
            'name' => 'Marketplace News',
            'slug' => 'marketplace-news',
            'is_active' => true,
        ]);

        $post = BlogPost::create([
            'user_id' => $author->id,
            'blog_category_id' => $category->id,
            'title' => 'Localized launch update',
            'slug' => 'localized-launch-update',
            'excerpt' => 'A short update.',
            'body' => 'Body copy',
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('localized.blog.show', [
            'locale' => 'sw',
            'slug' => $post->slug,
        ]))
            ->assertOk()
            ->assertSee('Localized launch update');
    }

    public function test_locale_prefixed_legacy_blog_slug_redirect_uses_the_correct_post_slug(): void
    {
        $author = User::factory()->create();
        $category = BlogCategory::create([
            'name' => 'Marketplace News',
            'slug' => 'marketplace-news',
            'is_active' => true,
        ]);

        $post = BlogPost::create([
            'user_id' => $author->id,
            'blog_category_id' => $category->id,
            'title' => 'Localized launch update',
            'slug' => 'localized-launch-update',
            'excerpt' => 'A short update.',
            'body' => 'Body copy',
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
        ]);

        $this->get('/sw/cetsy-blog/' . $post->slug)
            ->assertRedirect(route('localized.blog.show', [
                'locale' => 'sw',
                'slug' => $post->slug,
            ]));
    }

    public function test_sitemaps_include_locale_prefixed_marketplace_urls(): void
    {
        [, , $product] = $this->createLocalizedMarketplaceFixtures();

        $this->get(route('sitemap.static'))
            ->assertOk()
            ->assertSee(route('localized.home', ['locale' => 'en']), false)
            ->assertSee(route('localized.home', ['locale' => 'sw']), false)
            ->assertSee(route('localized.listings', ['locale' => 'sw']), false);

        $this->get(route('sitemap.products', ['page' => 1]))
            ->assertOk()
            ->assertSee(route('localized.listing.show', ['locale' => 'en', 'slug' => $product->slug]), false)
            ->assertSee(route('localized.listing.show', ['locale' => 'sw', 'slug' => $product->slug]), false);
    }

    public function test_welcome_buyer_mail_uses_the_recipient_locale_and_localized_home_link(): void
    {
        $buyer = User::factory()->create([
            'preferred_locale' => 'sw',
        ]);

        $html = (new WelcomeBuyerMail($buyer))->render();

        $this->assertStringContainsString('Karibu', $html);
        $this->assertStringContainsString('Anza Kununua', $html);
        $this->assertStringContainsString(route('localized.home', ['locale' => 'sw']), $html);
    }

    public function test_counter_offer_mail_uses_localized_listing_urls_and_translated_content(): void
    {
        [$seller, $buyer, $product] = $this->createLocalizedMarketplaceFixtures();

        $offer = Offer::create([
            'product_id' => $product->id,
            'buyer_id' => $buyer->id,
            'offer_price' => 22.50,
            'status' => 'pending',
            'is_counter_offer' => true,
            'original_offer_id' => null,
            'buyer_notes' => 'Original offer: USD 20.00',
            'seller_notes' => 'Bei hii ndiyo ya mwisho kwa sasa.',
        ]);

        $html = (new CounterOfferMail($offer, $product, $seller, $buyer))->render();

        $this->assertStringContainsString('Kuna counter offer mpya', $html);
        $this->assertStringContainsString('Kikombe cha Kiswahili', $html);
        $this->assertStringContainsString(route('localized.listing.show', ['locale' => 'sw', 'slug' => $product->slug]), $html);
    }

    private function createLocalizedMarketplaceFixtures(): array
    {
        $seller = User::factory()->create([
            'user_type' => User::TYPE_SELLER,
            'is_active' => true,
            'preferred_locale' => 'en',
        ]);

        $buyer = User::factory()->create([
            'user_type' => User::TYPE_BUYER,
            'is_active' => true,
            'preferred_locale' => 'sw',
        ]);

        $country = Country::create([
            'name' => 'Kenya',
            'country_code' => 'KE',
            'currency' => 'KES',
            'status' => 1,
        ]);

        $shop = Shop::create([
            'user_id' => $seller->id,
            'language' => 'English',
            'country' => (string) $country->id,
            'currency' => 'USD',
            'name' => 'English Shop',
            'name_translations' => ['sw' => 'Duka la Kiswahili'],
            'slug' => 'english-shop-' . $seller->id,
            'bio' => 'English shop bio.',
            'bio_translations' => ['sw' => 'Maelezo ya duka kwa Kiswahili.'],
            'announcement' => 'English announcement.',
            'announcement_translations' => ['sw' => 'Tangazo la duka kwa Kiswahili.'],
            'policies' => 'English policies.',
            'policies_translations' => ['sw' => 'Sera za duka kwa Kiswahili.'],
            'address' => '123 Main Street',
            'city' => 'Nairobi',
            'postal' => '00100',
            'is_active' => true,
        ]);

        $product = Product::create([
            'shop_id' => $shop->id,
            'country_id' => $country->id,
            'name' => 'English Mug',
            'name_translations' => ['sw' => 'Kikombe cha Kiswahili'],
            'slug' => 'english-mug-' . $seller->id,
            'description' => 'An English product description.',
            'description_translations' => ['sw' => 'Maelezo ya bidhaa kwa Kiswahili.'],
            'type' => Product::TYPE_PHYSICAL,
            'price' => 25,
            'stock' => 4,
            'is_active' => 1,
        ]);

        return [$seller, $buyer, $product];
    }
}
