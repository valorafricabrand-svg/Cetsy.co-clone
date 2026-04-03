<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Services\ProductPreviewImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductPreviewImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_digital_product_preview_image_is_generated_and_served(): void
    {
        Storage::fake('public');

        $product = $this->makeProduct(listingType: 'digital', type: 'physical');
        $fixturePath = base_path('public/assets/img/generic/1.jpg');
        $imageBytes = file_get_contents($fixturePath);

        $this->assertNotFalse($imageBytes, 'Fixture image should be readable.');

        Storage::disk('public')->put('products/test-art.jpg', $imageBytes);

        $product->update(['featured_image' => 'products/test-art.jpg']);

        $preview = app(ProductPreviewImageService::class)->buildProductPreview($product->fresh(), 'thumb');

        $this->assertNotNull($preview);
        $this->assertFileExists($preview['path']);
        $this->assertNotSame(md5($imageBytes), md5_file($preview['path']));

        $response = $this->get(route('preview.products.image', [
            'product' => $product,
            'variant' => 'thumb',
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'image/jpeg');
    }

    public function test_public_api_exposes_effective_type_and_preview_urls(): void
    {
        Storage::fake('public');

        $product = $this->makeProduct(listingType: 'digital', type: 'physical');
        Storage::disk('public')->put('products/test-art.jpg', file_get_contents(base_path('public/assets/img/generic/1.jpg')));

        $product->update([
            'featured_image' => 'products/test-art.jpg',
            'is_active' => 1,
        ]);

        $response = $this->getJson('/api/products');

        $response->assertOk();
        $response->assertJsonPath('data.0.id', $product->id);
        $response->assertJsonPath('data.0.type', 'digital');
        $response->assertJsonPath('data.0.effective_type', 'digital');
        $response->assertJsonPath('data.0.is_digital_preview', true);
        $response->assertJsonPath('data.0.thumbnail_url', Storage::disk('public')->url('products/test-art.jpg'));
        $this->assertStringContainsString(
            '/preview/products/',
            (string) data_get($response->json(), 'data.0.preview_thumbnail_url')
        );
    }

    private function makeProduct(string $listingType, ?string $type): Product
    {
        $seller = User::factory()->create(['user_type' => User::TYPE_SELLER]);

        $shop = Shop::create([
            'user_id' => $seller->id,
            'name' => 'Preview Shop ' . Str::random(6),
            'slug' => 'preview-shop-' . Str::lower(Str::random(8)),
            'language' => 'en',
            'country' => 'KE',
            'currency' => 'USD',
            'bank_account' => '1234567890',
            'routing_number' => '000111',
            'address' => '123 Seller St',
            'city' => 'Nairobi',
            'postal' => '00100',
        ]);

        $category = Category::create([
            'name' => 'Preview Category ' . Str::random(6),
            'slug' => 'preview-category-' . Str::lower(Str::random(8)),
            'listing_type' => $listingType,
        ]);

        return Product::create([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'name' => 'Preview Art ' . Str::random(6),
            'slug' => 'preview-art-' . Str::lower(Str::random(8)),
            'description' => 'Preview image test',
            'type' => $type,
            'price' => 15,
            'is_active' => 1,
        ]);
    }
}
