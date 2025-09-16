<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Shop;
use App\Models\Dispute;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DisputeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_dispute()
    {
        // Create test users
        $buyer = User::factory()->create(['user_type' => 'buyer']);
        $seller = User::factory()->create(['user_type' => 'seller']);
        
        // Create shop for seller
        $shop = Shop::create([
            'user_id' => $seller->id,
            'name' => 'Seller Shop',
            'slug' => 'seller-shop-' . uniqid(),
            'language' => 'en',
            'country' => 'KE',
            'currency' => 'USD',
            'address' => '123 Market St',
            'city' => 'Nairobi',
            'postal' => '00100',
        ]);
        
        // Create order
        $order = Order::create([
            'user_id' => $buyer->id,
            'shop_id' => $shop->id,
            'full_name' => 'Buyer Name',
            'email' => 'buyer@example.com',
            'phone' => '1234567890',
            'shipping_country_id' => 1,
            'shipping_address_1' => '123 Main St',
            'shipping_city' => 'Town',
            'shipping_state' => 'State',
            'shipping_postal_code' => '10000',
            'billing_same_as_shipping' => true,
            'shipping_method' => 'standard',
            'payment_method' => 'wallet',
            'subtotal' => 50,
            'total_amount' => 50,
            'status' => Order::STATUS_PENDING,
        ]);

        // Authenticate as buyer
        $this->actingAs($buyer);

        // Test dispute creation
        $response = $this->post('/disputes', [
            'order_id' => $order->id,
            'type' => 'customs_fees',
            'description' => 'Unexpected customs fees were charged',
        ]);

        $response->assertRedirect();
        
        // Verify dispute was created
        $this->assertDatabaseHas('disputes', [
            'order_id' => $order->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'type' => 'customs_fees',
            'status' => Dispute::STATUS_PENDING,
        ]);
    }

    public function test_user_can_view_their_disputes()
    {
        $user = User::factory()->create(['user_type' => 'buyer']);
        $this->actingAs($user);

        $response = $this->get('/disputes');
        $response->assertStatus(200);
        $response->assertViewIs('disputes.index');
    }

    public function test_dispute_requires_authentication()
    {
        $response = $this->get('/disputes');
        $response->assertRedirect('/login');
    }
}
