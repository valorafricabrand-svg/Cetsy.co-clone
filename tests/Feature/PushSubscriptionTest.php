<?php

namespace Tests\Feature;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_store_a_push_subscription(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('push-subscriptions.store'), [
            'endpoint' => 'https://push.example.test/subscription/123',
            'content_encoding' => 'aes128gcm',
            'keys' => [
                'p256dh' => 'demo-public-key',
                'auth' => 'demo-auth-token',
            ],
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->id,
            'endpoint_hash' => hash('sha256', 'https://push.example.test/subscription/123'),
            'content_encoding' => 'aes128gcm',
        ]);
    }

    public function test_authenticated_user_can_remove_a_push_subscription(): void
    {
        $user = User::factory()->create();

        $subscription = PushSubscription::create([
            'user_id' => $user->id,
            'endpoint_hash' => hash('sha256', 'https://push.example.test/subscription/remove-me'),
            'endpoint' => 'https://push.example.test/subscription/remove-me',
            'public_key' => 'demo-public-key',
            'auth_token' => 'demo-auth-token',
            'content_encoding' => 'aes128gcm',
        ]);

        $response = $this->actingAs($user)->postJson(route('push-subscriptions.destroy'), [
            'endpoint' => $subscription->endpoint,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertDatabaseMissing('push_subscriptions', [
            'id' => $subscription->id,
        ]);
    }
}
