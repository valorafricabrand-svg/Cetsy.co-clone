<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DigitalFile;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DigitalDownloadLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_open_external_digital_link_and_download_state_is_recorded(): void
    {
        [$seller, $product] = $this->makeDigitalProduct();
        $buyer = User::factory()->create(['user_type' => User::TYPE_BUYER]);

        $order = Order::create([
            'user_id' => $buyer->id,
            'shop_id' => $product->shop_id,
            'full_name' => 'Buyer Example',
            'email' => $buyer->email,
            'phone' => '123456789',
            'shipping_country_id' => 1,
            'shipping_address_1' => '123 Main St',
            'shipping_city' => 'Town',
            'shipping_state' => 'State',
            'shipping_postal_code' => '10001',
            'billing_same_as_shipping' => true,
            'shipping_method' => 'digital',
            'payment_method' => 'wallet',
            'subtotal' => 15,
            'total_amount' => 15,
            'status' => Order::STATUS_DELIVERED,
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 15,
            'download_count' => 0,
        ]);

        $file = DigitalFile::create([
            'product_id' => $product->id,
            'filename' => 'Watch video on Google Drive',
            'source_type' => DigitalFile::SOURCE_EXTERNAL_URL,
            'external_url' => 'https://drive.google.com/file/d/test-view',
        ]);

        $response = $this->actingAs($buyer)->get(route('digital-files.download', $file));

        $response->assertRedirect('https://drive.google.com/file/d/test-view');

        $item->refresh();
        $this->assertEquals(1, $item->download_count);
        $this->assertNotNull($item->downloaded_at);
    }

    public function test_seller_can_preview_their_own_external_digital_link_without_a_purchase(): void
    {
        [$seller, $product] = $this->makeDigitalProduct();

        $file = DigitalFile::create([
            'product_id' => $product->id,
            'filename' => 'Hosted lesson video',
            'source_type' => DigitalFile::SOURCE_EXTERNAL_URL,
            'external_url' => 'https://example.com/downloads/lesson-video',
        ]);

        $response = $this->actingAs($seller)->get(route('digital-files.download', $file));

        $response->assertRedirect('https://example.com/downloads/lesson-video');
    }

    private function makeDigitalProduct(): array
    {
        $seller = User::factory()->create(['user_type' => User::TYPE_SELLER]);

        $shop = Shop::create([
            'user_id' => $seller->id,
            'name' => 'Digital Shop ' . Str::random(6),
            'slug' => 'digital-shop-' . Str::lower(Str::random(8)),
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
            'name' => 'Digital Category ' . Str::random(6),
            'slug' => 'digital-category-' . Str::lower(Str::random(8)),
        ]);

        $product = Product::create([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'name' => 'Video Lesson ' . Str::random(6),
            'slug' => 'video-lesson-' . Str::lower(Str::random(8)),
            'description' => 'Digital lesson',
            'type' => 'digital',
            'price' => 15,
            'is_active' => 1,
        ]);

        return [$seller, $product];
    }
}
