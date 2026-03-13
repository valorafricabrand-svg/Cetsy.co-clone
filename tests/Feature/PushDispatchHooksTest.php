<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Message;
use App\Models\User;
use App\Services\WebPushService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PushDispatchHooksTest extends TestCase
{
    use RefreshDatabase;

    public function test_unread_non_message_activities_trigger_push_dispatch(): void
    {
        $user = User::factory()->create();

        $mock = Mockery::mock(WebPushService::class);
        $mock->shouldReceive('sendActivity')
            ->once()
            ->with(Mockery::on(fn (Activity $activity) => $activity->user_id === $user->id && $activity->type === Activity::TYPE_ORDER));

        $this->app->instance(WebPushService::class, $mock);

        Activity::create([
            'user_id' => $user->id,
            'is_read' => false,
            'description' => 'You received a new order from Demo Shop',
            'type' => Activity::TYPE_ORDER,
        ]);
    }

    public function test_message_creation_triggers_push_dispatch(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $mock = Mockery::mock(WebPushService::class);
        $mock->shouldReceive('sendMessage')
            ->once()
            ->with(Mockery::on(fn (Message $message) => $message->receiver_id === $receiver->id && $message->sender_id === $sender->id));

        $this->app->instance(WebPushService::class, $mock);

        Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'product_id' => null,
            'body' => 'A fresh message for push delivery.',
            'is_read' => false,
        ]);
    }

    public function test_read_and_message_type_activities_do_not_trigger_activity_push_dispatch(): void
    {
        $user = User::factory()->create();

        $mock = Mockery::mock(WebPushService::class);
        $mock->shouldReceive('sendActivity')->never();

        $this->app->instance(WebPushService::class, $mock);

        Activity::create([
            'user_id' => $user->id,
            'is_read' => true,
            'description' => 'Already handled',
            'type' => Activity::TYPE_ORDER,
        ]);

        Activity::create([
            'user_id' => $user->id,
            'is_read' => false,
            'description' => 'You received a new message',
            'type' => Activity::TYPE_MESSAGE,
        ]);
    }
}
