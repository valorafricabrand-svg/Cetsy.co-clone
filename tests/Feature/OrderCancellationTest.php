<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\OrderCancelledNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Http\Middleware\EnsureSellerHasActiveSubscription;
use Tests\TestCase;

class OrderCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_cancelling_order_records_reason_refunds_and_updates_status(): void
    {
        $this->withoutMiddleware([EnsureSellerHasActiveSubscription::class]);

        Notification::fake();

        $seller = User::factory()->create(['user_type' => 'seller']);
        $buyer  = User::factory()->create(['user_type' => 'buyer']);

        $shop = Shop::create([
            'user_id' => $seller->id,
            'name'    => 'Test Shop',
            'slug'    => 'test-shop-' . uniqid(),
            'language' => 'en',
            'country' => 'KE',
            'currency' => 'USD',
            'address' => '123 Market St',
            'city' => 'Nairobi',
            'postal' => '00100',
        ]);

        $order = Order::create([
            'user_id' => $buyer->id,
            'shop_id' => $shop->id,
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'shipping_country_id' => 1,
            'shipping_address_1' => '123 Main St',
            'shipping_city' => 'Town',
            'shipping_state' => 'State',
            'shipping_postal_code' => '00000',
            'billing_same_as_shipping' => true,
            'shipping_method' => 'standard',
            'payment_method' => 'wallet',
            'subtotal' => 100,
            'total_amount' => 100,
            'status' => Order::STATUS_PENDING,
        ]);

        $response = $this->actingAs($seller)->patch(route('seller.orders.cancel', $order), [
            'cancel_reason' => 'Out of stock',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $order->refresh();

        $this->assertEquals(Order::STATUS_CANCELLED, $order->status);
        $this->assertStringContainsString('Out of stock', $order->cancel_reason);

        $this->assertDatabaseCount('wallets', 0);

        Notification::assertSentTo([$buyer, $seller], OrderCancelledNotification::class);
    }
}
