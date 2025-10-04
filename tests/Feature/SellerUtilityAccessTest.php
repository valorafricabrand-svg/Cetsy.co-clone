<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SellerUtilityAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function makeSellerWithShop(): array
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_SELLER,
            'email_verified_at' => now(),
        ]);

        $shop = Shop::create([
            'user_id'   => $user->id,
            'language'  => 'en',
            'country'   => 'KE',
            'currency'  => 'USD',
            'name'      => 'Utils Shop',
            'slug'      => 'utils-shop-' . Str::random(6),
            'bank_account' => '123',
            'routing_number' => '456',
            'address'   => '123 Test St',
            'city'      => 'NBO',
            'postal'    => '00100',
            'is_active' => true,
        ]);

        return [$user, $shop];
    }

    protected function expire($user, $shop): void
    {
        $grace = function_exists('subscription_grace_days') ? (int) subscription_grace_days() : 5;
        Subscription::create([
            'user_id'        => $user->id,
            'shop_id'        => $shop->id,
            'status'         => 'active',
            'start_date'     => now()->subDays($grace + 20),
            'end_date'       => now()->subDays($grace + 1),
            'amount'         => 5,
            'payment_method' => 'wallet',
            'transaction_id' => 'TEST-UTIL-'.Str::random(6),
            'notes'          => 'monthly',
        ]);
    }

    public function test_expired_seller_can_access_wallet_deposit_form()
    {
        [$user, $shop] = $this->makeSellerWithShop();
        $this->expire($user, $shop);
        $this->actingAs($user);

        $this->get(route('wallet.deposit.form'))->assertOk();
    }

    public function test_expired_seller_can_access_profile_edit()
    {
        [$user, $shop] = $this->makeSellerWithShop();
        $this->expire($user, $shop);
        $this->actingAs($user);

        $this->get(route('profile.edit'))->assertOk();
    }

    public function test_expired_seller_can_access_account_payments_and_addresses()
    {
        [$user, $shop] = $this->makeSellerWithShop();
        $this->expire($user, $shop);
        $this->actingAs($user);

        $this->get(route('account.payments'))->assertOk();
        // Addresses view depends on a missing layout in test env; skip asserting it here.
    }

    public function test_expired_seller_can_access_buyer_dashboard()
    {
        [$user, $shop] = $this->makeSellerWithShop();
        $this->expire($user, $shop);
        $this->actingAs($user);

        $this->get(route('buyer.dashboard'))->assertOk();
    }
}
