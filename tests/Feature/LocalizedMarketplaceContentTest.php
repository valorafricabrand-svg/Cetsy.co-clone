<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizedMarketplaceContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_and_shop_pages_render_translated_content_for_selected_locale(): void
    {
        [$seller, $country, $shop] = $this->createSellerShop();
        $localeCookie = config('locales.cookie', 'locale');

        $shop->update([
            'name_translations' => ['sw' => 'Duka la Kiswahili'],
            'bio_translations' => ['sw' => 'Maelezo ya duka kwa wanunuzi wa Kiswahili.'],
            'announcement_translations' => ['sw' => 'Karibu kwenye duka letu.'],
            'policies_translations' => ['sw' => 'Sera za kurejesha na kusafirisha.'],
        ]);

        $product = Product::create([
            'shop_id' => $shop->id,
            'country_id' => $country->id,
            'name' => 'English Mug',
            'name_translations' => ['sw' => 'Kikombe cha Kiswahili'],
            'slug' => 'english-mug',
            'description' => 'An English product description.',
            'description_translations' => ['sw' => 'Maelezo ya bidhaa kwa Kiswahili.'],
            'type' => Product::TYPE_PHYSICAL,
            'price' => 25,
            'stock' => 4,
            'is_active' => 1,
        ]);

        $this->withCookie($localeCookie, 'sw')
            ->get(route('listing.show', $product->slug))
            ->assertOk()
            ->assertSee('Kikombe cha Kiswahili')
            ->assertSee('Maelezo ya bidhaa kwa Kiswahili.')
            ->assertSee('Duka la Kiswahili');

        $this->withCookie($localeCookie, 'sw')
            ->get(route('shop.show', $shop->slug))
            ->assertOk()
            ->assertSee('Duka la Kiswahili')
            ->assertSee('Maelezo ya duka kwa wanunuzi wa Kiswahili.')
            ->assertSee('Kikombe cha Kiswahili');
    }

    public function test_marketplace_search_matches_translated_product_content(): void
    {
        [, $country, $shop] = $this->createSellerShop();
        $localeCookie = config('locales.cookie', 'locale');

        Product::create([
            'shop_id' => $shop->id,
            'country_id' => $country->id,
            'name' => 'English Mug',
            'name_translations' => ['sw' => 'Kikombe cha Kiswahili'],
            'slug' => 'translated-search-product',
            'description' => 'English only description.',
            'description_translations' => ['sw' => 'Kikombe hiki kinafaa kwa kahawa.'],
            'type' => Product::TYPE_PHYSICAL,
            'price' => 18,
            'stock' => 3,
            'is_active' => 1,
        ]);

        $this->withCookie($localeCookie, 'sw')
            ->get(route('listings', ['q' => 'Kikombe']))
            ->assertOk()
            ->assertSee('Kikombe cha Kiswahili');
    }

    public function test_seller_can_store_and_update_translated_marketplace_content(): void
    {
        [$seller, $country, $shop] = $this->createSellerShop();

        $this->withoutMiddleware([
            \App\Http\Middleware\RequireKycAfterTwoSales::class,
            \App\Http\Middleware\EnsureSellerHasActiveSubscription::class,
        ]);

        $this->actingAs($seller)
            ->post(route('products.store'), [
                'name' => 'English Mug',
                'type' => Product::TYPE_PHYSICAL,
                'description' => 'English description',
                'price' => '24.50',
                'stock' => '5',
                'country_id' => $country->id,
                'translations' => [
                    'name' => ['sw' => 'Kikombe cha Kiswahili'],
                    'description' => ['sw' => 'Maelezo ya Kiswahili'],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $product = Product::query()->latest('id')->first();

        $this->assertSame('Kikombe cha Kiswahili', $product->name_translations['sw'] ?? null);
        $this->assertSame('Maelezo ya Kiswahili', $product->description_translations['sw'] ?? null);

        $this->actingAs($seller)
            ->patch(route('seller.shops.update', $shop), [
                'language' => 'English',
                'country' => (string) $country->id,
                'currency' => 'USD',
                'name' => 'English Shop',
                'slug' => $shop->slug,
                'bio' => 'English bio',
                'announcement' => 'English announcement',
                'policies' => 'English policies',
                'address' => '123 Main Street',
                'city' => 'Nairobi',
                'postal' => '00100',
                'password' => 'password',
                'enable_2fa' => '0',
                'translations' => [
                    'name' => ['sw' => 'Duka la Kiswahili'],
                    'bio' => ['sw' => 'Maelezo ya duka'],
                    'announcement' => ['sw' => 'Tangazo la duka'],
                    'policies' => ['sw' => 'Sera za duka'],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('seller.shops.show', $shop->fresh()));

        $shop->refresh();

        $this->assertSame('Duka la Kiswahili', $shop->name_translations['sw'] ?? null);
        $this->assertSame('Maelezo ya duka', $shop->bio_translations['sw'] ?? null);
        $this->assertSame('Tangazo la duka', $shop->announcement_translations['sw'] ?? null);
        $this->assertSame('Sera za duka', $shop->policies_translations['sw'] ?? null);
    }

    private function createSellerShop(): array
    {
        $seller = User::factory()->create([
            'user_type' => User::TYPE_SELLER,
            'is_active' => true,
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
            'slug' => 'english-shop-' . $seller->id,
            'bio' => 'English shop bio.',
            'announcement' => 'English shop announcement.',
            'policies' => 'English shop policies.',
            'address' => '123 Main Street',
            'city' => 'Nairobi',
            'postal' => '00100',
            'is_active' => true,
        ]);

        return [$seller, $country, $shop];
    }
}
