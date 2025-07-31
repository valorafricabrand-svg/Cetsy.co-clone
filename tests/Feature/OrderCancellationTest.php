<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\OrderCancelledNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderCancellationTest extends TestCase
{
    public function test_seller_cancelling_order_records_reason_refunds_and_updates_status(): void
    {
        $this->artisan('migrate', ['--path' => 'database/migrations/0001_01_01_000000_create_users_table.php']);
        $this->artisan('migrate', ['--path' => 'database/migrations/2025_05_17_111021_create_shops_table.php']);
        $this->artisan('migrate', ['--path' => 'database/migrations/2025_06_09_060633_create_wallets_table.php']);

        Schema::create('shipping_profiles', function ($table) {
            $table->id();
            $table->foreignId('shop_id')->nullable();
            $table->string('name');
            $table->string('country_id')->nullable();
            $table->decimal('base_rate', 8, 2)->default(0);
            $table->integer('delivery_days')->default(0);
            $table->boolean('pickup_available')->default(false);
            $table->timestamps();
        });

        Schema::create('orders', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('shop_id');
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->unsignedBigInteger('shipping_country_id');
            $table->string('shipping_address_1');
            $table->string('shipping_city');
            $table->string('shipping_state');
            $table->string('shipping_postal_code');
            $table->boolean('billing_same_as_shipping')->default(true);
            $table->string('shipping_method');
            $table->string('payment_method')->default('paypal');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('pending');
            $table->text('cancel_reason')->nullable();
            $table->timestamps();
        });


        Notification::fake();

        $seller = User::factory()->create();
        $buyer  = User::factory()->create();

        $shop = Shop::create([
            'user_id' => $seller->id,
            'name'    => 'Test Shop',
            'slug'    => 'test-shop',
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

        $response = $this->actingAs($seller)->patch(route('orders.cancel', $order), [
            'cancel_reason' => 'Out of stock',
        ]);

        $response->assertRedirect();

        $order->refresh();

        $this->assertEquals(Order::STATUS_REFUNDED, $order->status);
        $this->assertEquals('Out of stock', $order->cancel_reason);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $buyer->id,
            'credit' => 100,
            'description' => 'Order refund',
        ]);

        Notification::assertSentTo([$buyer, $seller], OrderCancelledNotification::class);
    }
}
