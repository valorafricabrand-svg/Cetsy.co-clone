<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SellerSubscriptionRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function makeSellerWithShop(): array
    {
        /** @var User $user */
        $user = User::factory()->create([
            'user_type' => User::TYPE_SELLER,
            'email_verified_at' => now(),
        ]);

        $shop = Shop::create([
            'user_id'   => $user->id,
            'language'  => 'en',
            'country'   => 'KE',
            'currency'  => 'USD',
            'name'      => 'Test Shop',
            'slug'      => 'test-shop-' . Str::random(6),
            'bank_account' => '123',
            'routing_number' => '456',
            'address'   => '123 Test St',
            'city'      => 'NBO',
            'postal'    => '00100',
            'is_active' => true,
        ]);

        return [$user, $shop];
    }

    public function test_expired_seller_is_redirected_to_subscription_page()
    {
        [$user, $shop] = $this->makeSellerWithShop();

        // Create an expired subscription outside the grace period
        $grace = function_exists('subscription_grace_days') ? (int) subscription_grace_days() : 5;
        Subscription::create([
            'user_id'        => $user->id,
            'shop_id'        => $shop->id,
            'status'         => 'active',
            'start_date'     => now()->subDays($grace + 20),
            'end_date'       => now()->subDays($grace + 1),
            'amount'         => 5,
            'payment_method' => 'wallet',
            'transaction_id' => 'TEST-EXP-'.Str::random(6),
            'notes'          => 'monthly',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('seller.dashboard'));

        $response->assertRedirect(route('seller.subscription'));
        $response->assertSessionHas('error');

        $this->assertFalse($shop->fresh()->is_active, 'Shop should be set inactive when subscription is not active');
    }

    public function test_expired_seller_can_access_subscription_routes()
    {
        [$user, $shop] = $this->makeSellerWithShop();

        // Expire any existing or implicit subscription context if needed (not required for this test)
        $this->actingAs($user);

        // GET subscription page should be accessible
        $resp = $this->get(route('seller.subscription'));
        $resp->assertOk();
        $resp->assertSee('Seller Subscription');

        // POST subscribe to choose plan should return the payment view
        $resp2 = $this->post(route('seller.subscription.subscribe'), ['plan' => 'monthly']);
        $resp2->assertOk();
    }
}
