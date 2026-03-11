<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationPulseTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_receives_live_notification_payload()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_BUYER,
            'email_verified_at' => now(),
        ]);

        $sender = User::factory()->create([
            'user_type' => User::TYPE_SELLER,
            'email_verified_at' => now(),
        ]);

        $message = Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $user->id,
            'product_id' => null,
            'body' => 'Hello from the seller side.',
            'is_read' => false,
        ]);

        $activity = Activity::create([
            'user_id' => $user->id,
            'is_read' => false,
            'description' => 'You received a new order from Demo Shop',
            'type' => Activity::TYPE_ORDER,
            'related_id' => 123,
            'related_type' => 'order',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('notifications.pulse'));

        $response->assertOk();
        $response->assertJsonPath('notif', 1);
        $response->assertJsonPath('msg', 1);
        $response->assertJsonPath('latest_notification.id', $activity->id);
        $response->assertJsonPath('latest_notification.type', Activity::TYPE_ORDER);
        $response->assertJsonPath('latest_message.id', $message->id);
        $response->assertJsonPath('latest_message.sender_name', $sender->name);
    }
}
