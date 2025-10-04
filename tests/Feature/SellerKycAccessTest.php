<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SellerKycAccessTest extends TestCase
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
            'name'      => 'KYC Shop',
            'slug'      => 'kyc-shop-' . Str::random(6),
            'bank_account' => '123',
            'routing_number' => '456',
            'address'   => '123 Test St',
            'city'      => 'NBO',
            'postal'    => '00100',
            'is_active' => true,
        ]);

        return [$user, $shop];
    }

    protected function expireSubscription($user, $shop): void
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
            'transaction_id' => 'TEST-KYC-'.Str::random(6),
            'notes'          => 'monthly',
        ]);
    }

    public function test_expired_seller_can_access_kyc_pages()
    {
        [$user, $shop] = $this->makeSellerWithShop();
        $this->expireSubscription($user, $shop);

        $this->actingAs($user);

        $this->get(route('seller.kyc'))->assertRedirect(route('seller.kyc.info'));
        $this->get(route('seller.kyc.info'))->assertOk();
        $this->get(route('seller.kyc.documents'))->assertOk();
    }
}
