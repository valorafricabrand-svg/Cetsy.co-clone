<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Models\VariationOption;
use App\Models\VariationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductVariationManagePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.key' => 'base64:' . base64_encode(str_repeat('a', 32)),
        ]);
    }

    public function test_manage_page_renders_inside_the_seller_listing_editor(): void
    {
        [$seller, $product] = $this->makeSellerProduct();

        $type = VariationType::create([
            'product_id' => $product->id,
            'name' => 'Color',
            'affects_price' => true,
        ]);

        VariationOption::create([
            'variation_type_id' => $type->id,
            'value' => 'Blue',
        ]);

        $response = $this->actingAs($seller)->get(route('products.variations.manage', [
            'product' => $product,
            'type' => $type,
        ]));

        $response->assertOk();
        $response->assertSee(route('products.pricing', $product), false);
        $response->assertSee(route('products.variations', $product), false);
        $response->assertSee(route('products.settings', $product), false);
        $response->assertSee('Manage:');
        $response->assertSee('Color');
        $response->assertSee('Add Variant');
    }

    public function test_manage_page_returns_404_for_a_type_from_another_product(): void
    {
        [$seller, $product] = $this->makeSellerProduct();
        [, $otherProduct] = $this->makeSellerProduct();

        $type = VariationType::create([
            'product_id' => $otherProduct->id,
            'name' => 'Size',
        ]);

        $response = $this->actingAs($seller)->get(route('products.variations.manage', [
            'product' => $product,
            'type' => $type,
        ]));

        $response->assertNotFound();
    }

    private function makeSellerProduct(array $overrides = []): array
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
            'type' => 'physical',
            'price' => 20,
            'renewal_type' => 'automatic',
        ], $overrides));

        return [$seller, $product];
    }
}
