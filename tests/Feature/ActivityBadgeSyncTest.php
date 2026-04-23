<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ActivityBadgeSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_favorites_page_only_clears_personal_wishlist_alerts(): void
    {
        [$seller, $sellerShop] = $this->makeSellerWithShop();
        [$otherSeller, $otherShop] = $this->makeSellerWithShop();
        $buyer = User::factory()->create([
            'user_type' => User::TYPE_BUYER,
            'email_verified_at' => now(),
        ]);

        $shopProduct = $this->makeProduct($sellerShop, ['name' => 'Shop Favorite Target']);
        $savedProduct = $this->makeProduct($otherShop, ['name' => 'Saved Favorite Target']);

        Wishlist::create([
            'user_id' => $seller->id,
            'product_id' => $savedProduct->id,
        ]);

        $personalFavoriteActivity = Activity::create([
            'user_id' => $seller->id,
            'is_read' => false,
            'description' => 'You added Saved Favorite Target to your wishlist',
            'type' => Activity::TYPE_WISHLIST,
            'related_id' => $savedProduct->id,
            'related_type' => 'product',
        ]);

        $shopFavoriteActivity = Activity::create([
            'user_id' => $seller->id,
            'is_read' => false,
            'description' => 'Buyer favorited your item',
            'type' => Activity::TYPE_WISHLIST,
            'related_id' => $shopProduct->id,
            'related_type' => 'product',
            'causer_id' => $buyer->id,
            'causer_type' => User::class,
        ]);

        $response = $this->actingAs($seller)->get(route('buyer.favorites'));

        $response->assertOk();
        $this->assertTrue($personalFavoriteActivity->fresh()->is_read);
        $this->assertFalse($shopFavoriteActivity->fresh()->is_read);
    }

    public function test_seller_offers_page_only_clears_offer_alerts_for_their_shop(): void
    {
        [$seller, $sellerShop] = $this->makeSellerWithShop(true);
        [$otherSeller, $otherShop] = $this->makeSellerWithShop();
        $buyer = User::factory()->create([
            'user_type' => User::TYPE_BUYER,
            'email_verified_at' => now(),
        ]);

        $sellerProduct = $this->makeProduct($sellerShop, ['name' => 'Seller Offer Product']);
        $externalProduct = $this->makeProduct($otherShop, ['name' => 'External Offer Product']);

        $shopOffer = Offer::create([
            'product_id' => $sellerProduct->id,
            'buyer_id' => $buyer->id,
            'offer_price' => 12.50,
            'status' => 'pending',
            'is_counter_offer' => false,
        ]);

        $buyerOffer = Offer::create([
            'product_id' => $externalProduct->id,
            'buyer_id' => $seller->id,
            'offer_price' => 15.00,
            'status' => 'pending',
            'is_counter_offer' => false,
        ]);

        $sellerOfferActivity = Activity::create([
            'user_id' => $seller->id,
            'is_read' => false,
            'description' => 'You received a new offer for your shop product',
            'type' => Activity::TYPE_OFFER,
            'related_id' => $shopOffer->id,
            'related_type' => 'offer',
        ]);

        $buyerOfferActivity = Activity::create([
            'user_id' => $seller->id,
            'is_read' => false,
            'description' => 'Your offer was updated on another shop',
            'type' => Activity::TYPE_OFFER,
            'related_id' => $buyerOffer->id,
            'related_type' => 'offer',
        ]);

        $response = $this->actingAs($seller)->get(route('seller.offers.index'));

        $response->assertOk();
        $this->assertTrue($sellerOfferActivity->fresh()->is_read);
        $this->assertFalse($buyerOfferActivity->fresh()->is_read);
    }

    public function test_buyer_offers_page_marks_buyer_offer_alerts_as_read(): void
    {
        $buyer = User::factory()->create([
            'user_type' => User::TYPE_BUYER,
            'email_verified_at' => now(),
        ]);
        [$seller, $shop] = $this->makeSellerWithShop();
        $product = $this->makeProduct($shop, ['name' => 'Buyer Offer Product']);

        $offer = Offer::create([
            'product_id' => $product->id,
            'buyer_id' => $buyer->id,
            'offer_price' => 18.00,
            'status' => 'pending',
            'is_counter_offer' => false,
        ]);

        $activity = Activity::create([
            'user_id' => $buyer->id,
            'is_read' => false,
            'description' => 'You sent a counter offer for Buyer Offer Product',
            'type' => Activity::TYPE_OFFER,
            'related_id' => $offer->id,
            'related_type' => 'offer',
        ]);

        $response = $this->actingAs($buyer)->get(route('buyer.offers'));

        $response->assertOk();
        $this->assertTrue($activity->fresh()->is_read);
    }

    private function makeSellerWithShop(bool $withActiveSubscription = false): array
    {
        $seller = User::factory()->create([
            'user_type' => User::TYPE_SELLER,
            'email_verified_at' => now(),
        ]);

        $shop = Shop::create([
            'user_id' => $seller->id,
            'name' => 'Shop ' . Str::random(6),
            'slug' => 'shop-' . Str::lower(Str::random(8)),
            'language' => 'en',
            'country' => 'US',
            'currency' => 'USD',
            'bank_account' => '123456789',
            'routing_number' => '987654321',
            'address' => '123 Main St',
            'city' => 'Springfield',
            'postal' => '12345',
            'is_active' => true,
        ]);

        if ($withActiveSubscription) {
            Subscription::create([
                'user_id' => $seller->id,
                'shop_id' => $shop->id,
                'status' => 'active',
                'start_date' => now()->subDay(),
                'end_date' => now()->addDays(30),
                'amount' => 5,
                'payment_method' => 'wallet',
                'transaction_id' => 'TEST-' . Str::upper(Str::random(8)),
                'notes' => 'monthly',
            ]);
        }

        return [$seller, $shop];
    }

    private function makeProduct(Shop $shop, array $overrides = []): Product
    {
        $category = Category::create([
            'name' => 'Category ' . Str::random(6),
            'slug' => 'category-' . Str::lower(Str::random(8)),
            'listing_fee' => 5.00,
            'listing_frequency' => 4,
        ]);

        return Product::create(array_merge([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'name' => 'Product ' . Str::random(6),
            'slug' => 'product-' . Str::lower(Str::random(8)),
            'description' => 'Test product',
            'type' => Product::TYPE_DIGITAL,
            'price' => 25.00,
            'renewal_type' => 'automatic',
            'is_active' => true,
        ], $overrides));
    }
}
