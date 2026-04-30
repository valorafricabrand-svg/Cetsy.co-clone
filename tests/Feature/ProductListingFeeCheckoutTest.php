<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductListingFeeCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_fee_checkout_can_be_opened_with_get_for_digital_listings(): void
    {
        [$seller, $product] = $this->makeDigitalListing();

        $response = $this->actingAs($seller)->get(route('products.pay-fee', $product));

        $response
            ->assertOk()
            ->assertSee('Complete Your Payment')
            ->assertSee('4-Month');
    }

    public function test_listing_fee_checkout_still_accepts_posted_plan(): void
    {
        [$seller, $product] = $this->makeDigitalListing();

        $response = $this->actingAs($seller)->post(route('products.pay-fee.submit', $product), [
            'plan' => '4months',
        ]);

        $response
            ->assertOk()
            ->assertSee('Complete Your Payment')
            ->assertSee('4-Month');
    }

    private function makeDigitalListing(): array
    {
        $seller = User::factory()->create([
            'user_type' => User::TYPE_SELLER,
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
        ]);

        $category = Category::create([
            'name' => 'Digital Art ' . Str::random(6),
            'slug' => 'digital-art-' . Str::lower(Str::random(8)),
            'listing_type' => 'digital',
            'listing_fee' => 0.25,
            'listing_frequency' => 4,
        ]);

        $product = Product::create([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'name' => 'Digital African Art ' . Str::random(6),
            'slug' => 'digital-african-art-' . Str::lower(Str::random(8)),
            'description' => 'Printable artwork.',
            'type' => Product::TYPE_DIGITAL,
            'price' => 20,
            'is_active' => 0,
        ]);

        return [$seller, $product];
    }
}
