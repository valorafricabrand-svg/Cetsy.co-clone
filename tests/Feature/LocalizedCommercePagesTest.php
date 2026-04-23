<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Message;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizedCommercePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_orders_page_renders_translated_ui_and_localized_shop_names(): void
    {
        [$seller, $buyer, $country, $shop, $product] = $this->createMarketplaceFixtures();
        $localeCookie = config('locales.cookie', 'locale');

        Order::create([
            'user_id' => $buyer->id,
            'shop_id' => $shop->id,
            'full_name' => $buyer->name,
            'email' => $buyer->email,
            'phone' => '0712345678',
            'shipping_country_id' => $country->id,
            'shipping_address_1' => '123 Main Street',
            'shipping_city' => 'Nairobi',
            'shipping_state' => 'Nairobi',
            'shipping_postal_code' => '00100',
            'billing_same_as_shipping' => true,
            'shipping_method' => 'standard',
            'payment_method' => 'paypal',
            'subtotal' => 25.00,
            'total_amount' => 25.00,
            'status' => Order::STATUS_PENDING,
        ]);

        $this->actingAs($buyer)
            ->withCookie($localeCookie, 'sw')
            ->get(route('account.orders'))
            ->assertOk()
            ->assertSee('Oda Zangu')
            ->assertSee('Fuatilia hali, maendeleo ya malipo na masasisho ya usafirishaji kwa oda zako zote.')
            ->assertSee('Duka la Kiswahili')
            ->assertSee('Inasubiri')
            ->assertSee('Malipo yanasubiriwa');
    }

    public function test_buyer_message_pages_render_translated_ui_and_localized_product_content(): void
    {
        [$seller, $buyer, , $shop, $product] = $this->createMarketplaceFixtures();
        $localeCookie = config('locales.cookie', 'locale');

        Message::create([
            'sender_id' => $seller->id,
            'receiver_id' => $buyer->id,
            'product_id' => $product->id,
            'body' => 'Karibu kwenye duka letu.',
            'is_read' => false,
        ]);

        Offer::create([
            'product_id' => $product->id,
            'buyer_id' => $buyer->id,
            'offer_price' => 20.00,
            'status' => 'pending',
            'is_counter_offer' => false,
        ]);

        $conversationId = $product->id . '-' . $seller->id;

        $this->actingAs($buyer)
            ->withCookie($localeCookie, 'sw')
            ->get(route('buyer.messages.index'))
            ->assertOk()
            ->assertSee('Mazungumzo')
            ->assertSee('Tafuta mtumiaji, bidhaa au ujumbe...')
            ->assertSee('Kikombe cha Kiswahili')
            ->assertSee('Duka la Kiswahili')
            ->assertSee('Fungua orodha');

        $this->actingAs($buyer)
            ->withCookie($localeCookie, 'sw')
            ->get(route('buyer.messages.show', $conversationId))
            ->assertOk()
            ->assertSee('Duka la Kiswahili')
            ->assertSee('Kikombe cha Kiswahili')
            ->assertSee('Bei iliyowekwa')
            ->assertSee('Ofa ya hivi karibuni')
            ->assertSee('Rudi kwenye Mazungumzo')
            ->assertSee('Tuma Ujumbe');
    }

    public function test_buyer_offers_page_renders_translated_offer_lifecycle_copy(): void
    {
        [$seller, $buyer, , $shop, $product] = $this->createMarketplaceFixtures();
        $localeCookie = config('locales.cookie', 'locale');

        Offer::create([
            'product_id' => $product->id,
            'buyer_id' => $buyer->id,
            'offer_price' => 19.50,
            'status' => 'pending',
            'is_counter_offer' => false,
        ]);

        $this->actingAs($buyer)
            ->withCookie($localeCookie, 'sw')
            ->get(route('buyer.offers'))
            ->assertOk()
            ->assertSee('Jumla ya Bidhaa')
            ->assertSee('Ofa Zinazosubiri')
            ->assertSee('Toa Ofa Mpya')
            ->assertSee('Kikombe cha Kiswahili')
            ->assertSee('Duka la Kiswahili')
            ->assertSee('Ofa Yako ya Hivi Karibuni');
    }

    private function createMarketplaceFixtures(): array
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

        return [$seller, $buyer, $country, $shop, $product];
    }
}
