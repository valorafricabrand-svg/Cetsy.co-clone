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
        $shop = Shop::factory()->create(['user_id' => $seller->id]);
        
        // Create order
        $order = Order::factory()->create([
            'user_id' => $buyer->id,
            'shop_id' => $shop->id,
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
            'status' => 'pending',
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
