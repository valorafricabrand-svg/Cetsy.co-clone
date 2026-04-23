<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Shop;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SellerNotificationsAccessTest extends TestCase
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
            'name'      => 'Notify Shop',
            'slug'      => 'notify-shop-' . Str::random(6),
            'bank_account' => '123',
            'routing_number' => '456',
            'address'   => '123 Test St',
            'city'      => 'NBO',
            'postal'    => '00100',
            'is_active' => true,
        ]);

        return [$user, $shop];
    }

    public function test_expired_seller_can_view_notifications_page()
    {
        [$user, $shop] = $this->makeSellerWithShop();

        // Create an activity for this user
        Activity::create([
            'user_id' => $user->id,
            'is_read' => false,
            'description' => 'Test notification',
            'type' => Activity::TYPE_GENERAL,
        ]);

        // Create an expired subscription to simulate deactivation
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

        // Notifications index should be accessible without active subscription
        $resp = $this->get(route('seller.notifications.index'));
        $resp->assertOk();
    }

    public function test_expired_seller_can_mark_notification_as_read()
    {
        [$user, $shop] = $this->makeSellerWithShop();

        $activity = Activity::create([
            'user_id' => $user->id,
            'is_read' => false,
            'description' => 'Unread',
            'type' => Activity::TYPE_GENERAL,
        ]);

        // Simulate expired subscription
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

        $resp = $this->post(route('seller.notifications.mark-read', $activity->id));
        $resp->assertSessionHas('success');
        $this->assertTrue($activity->fresh()->is_read);
    }

    public function test_seller_notifications_ajax_returns_load_more_payload()
    {
        [$user, $shop] = $this->makeSellerWithShop();

        foreach (range(1, 25) as $index) {
            Activity::create([
                'user_id' => $user->id,
                'is_read' => $index > 3,
                'description' => "Notification {$index}",
                'type' => Activity::TYPE_GENERAL,
                'created_at' => now()->subMinutes($index),
                'updated_at' => now()->subMinutes($index),
            ]);
        }

        $this->actingAs($user);

        $response = $this->getJson(route('seller.notifications.index', ['page' => 2]));

        $response->assertOk()
            ->assertJson([
                'has_more_pages' => false,
                'shown_count' => 25,
                'total_count' => 25,
                'unread_count' => 3,
            ]);

        $payload = $response->json();

        $this->assertNull($payload['next_page_url']);
        $this->assertStringContainsString('Notification 21', $payload['html']);
        $this->assertStringNotContainsString('Notification 20', $payload['html']);
    }
}
