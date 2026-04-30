<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductMediaUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_media_page_allows_multiple_file_selection(): void
    {
        [$seller, $product] = $this->makeSellerProduct();

        $response = $this->actingAs($seller)->get(route('products.media', $product));

        $response->assertOk();
        $this->assertMatchesRegularExpression(
            '/<input[^>]+id="productMediaUploadInput"[^>]+name="media\[\]"[^>]+multiple/s',
            $response->getContent()
        );
    }

    public function test_product_media_upload_accepts_multiple_files(): void
    {
        Storage::fake('public');

        [$seller, $product] = $this->makeSellerProduct();

        $response = $this->actingAs($seller)->post(route('media.upload', $product), [
            'media' => [
                UploadedFile::fake()->image('front.jpg'),
                UploadedFile::fake()->image('back.png'),
            ],
        ]);

        $response->assertRedirect();
        $this->assertCount(2, $product->fresh()->media);

        foreach ($product->fresh()->media as $media) {
            Storage::disk('public')->assertExists($media->url);
        }
    }

    private function makeSellerProduct(): array
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
            'listing_type' => 'products',
            'listing_fee' => 0.25,
            'listing_frequency' => 4,
        ]);

        $product = Product::create([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'name' => 'Listing ' . Str::random(6),
            'slug' => 'listing-' . Str::lower(Str::random(8)),
            'description' => 'Test listing',
            'type' => Product::TYPE_PHYSICAL,
            'price' => 20,
            'stock' => 5,
            'is_active' => 1,
        ]);

        return [$seller, $product];
    }
}
