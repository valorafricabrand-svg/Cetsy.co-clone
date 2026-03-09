<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ListingActivationStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_update_normalizes_unpaid_listing_back_to_pending(): void
    {
        [$seller, $product] = $this->makeSellerProduct([
            'is_active' => 2,
            'listing_paid_at' => null,
            'next_due_date' => null,
        ]);

        $response = $this->actingAs($seller)->patch(route('products.settings.update', $product), [
            'is_active' => 2,
            'renewal_type' => 'automatic',
            'slug' => $product->slug,
            'tags' => '',
        ]);

        $response->assertRedirect();

        $product->refresh();
        $this->assertSame(0, (int) $product->is_active);
    }

    public function test_unpaid_paused_listing_shows_payment_options_instead_of_renewal_wait_message(): void
    {
        [$seller, $product] = $this->makeSellerProduct([
            'is_active' => 2,
            'listing_paid_at' => null,
            'next_due_date' => null,
        ]);

        $response = $this->actingAs($seller)->get(route('products.show', $product));

        $response->assertOk();
        $response->assertSee('This listing is not live yet. Pay the fee below to activate it.');
        $response->assertSee('Pay 4-Month');
        $response->assertDontSee('eligible for renewal on -');
    }

    private function makeSellerProduct(array $overrides = []): array
    {
        $seller = User::factory()->create(['user_type' => User::TYPE_SELLER]);

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
            'name' => 'Category ' . Str::random(6),
            'slug' => 'category-' . Str::lower(Str::random(8)),
            'listing_fee' => 5.00,
            'listing_frequency' => 4,
        ]);

        $product = Product::create(array_merge([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'name' => 'Listing ' . Str::random(6),
            'slug' => 'listing-' . Str::lower(Str::random(8)),
            'description' => 'Test listing',
            'type' => 'digital',
            'price' => 20,
            'renewal_type' => 'automatic',
        ], $overrides));

        return [$seller, $product];
    }
}
