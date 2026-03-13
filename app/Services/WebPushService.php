<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Message;
use App\Models\PushSubscription as PushSubscriptionModel;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Minishlink\WebPush\Subscription as WebPushSubscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    public function isConfigured(): bool
    {
        return (bool) config('webpush.enabled')
            && filled(config('webpush.vapid.public_key'))
            && filled(config('webpush.vapid.private_key'))
            && filled(config('webpush.vapid.subject'));
    }

    public function sendActivity(Activity $activity): int
    {
        if (! $this->isConfigured() || ! $activity->user_id || $activity->is_read || $activity->type === Activity::TYPE_MESSAGE) {
            return 0;
        }

        $user = $activity->relationLoaded('user') ? $activity->user : $activity->user()->first();
        if (! $user) {
            return 0;
        }

        $title = trim((string) ($activity->title ?: $activity->description ?: $activity->message ?: 'Notification'));
        $body = Str::limit(trim((string) ($activity->description ?: $activity->message ?: $activity->title ?: 'Open Cetsy to view the latest update.')), 160);
        $link = route('notifications.index');
        $action = 'Open';

        try {
            $link = NotificationRouteService::getRouteForNotification($activity, $user) ?: $link;
        } catch (\Throwable $e) {
            $link = route('notifications.index');
        }

        try {
            $action = NotificationRouteService::getLinkText($activity, $user) ?: $action;
        } catch (\Throwable $e) {
            $action = 'Open';
        }

        return $this->sendToUser($user, [
            'kind' => 'activity',
            'id' => (int) $activity->id,
            'title' => $title,
            'body' => $body,
            'url' => $link,
            'tag' => 'cetsy-activity-' . ($activity->type ?: 'general'),
            'icon' => (string) config('webpush.icon'),
            'badge' => (string) config('webpush.badge'),
            'notification' => [
                'id' => (int) $activity->id,
                'type' => (string) ($activity->type ?? ''),
                'title' => $title,
                'description' => $body,
                'link' => $link,
                'action' => $action,
                'created_at' => optional($activity->created_at)->toIso8601String(),
            ],
        ]);
    }

    public function sendMessage(Message $message): int
    {
        if (! $this->isConfigured() || ! $message->receiver_id) {
            return 0;
        }

        $message->loadMissing('sender:id,name', 'receiver:id');

        $receiver = $message->receiver;
        if (! $receiver) {
            return 0;
        }

        $activity = new Activity([
            'type' => Activity::TYPE_MESSAGE,
            'related_id' => $message->id,
        ]);

        $link = route('notifications.index');
        try {
            $link = NotificationRouteService::getRouteForNotification($activity, $receiver) ?: $link;
        } catch (\Throwable $e) {
            $link = route('notifications.index');
        }

        $senderName = trim((string) (optional($message->sender)->name ?: 'a customer'));
        $title = 'New message from ' . $senderName;
        $body = Str::limit(trim((string) ($message->body ?: 'Open your inbox to reply.')), 160);

        return $this->sendToUser($receiver, [
            'kind' => 'message',
            'id' => (int) $message->id,
            'title' => $title,
            'body' => $body,
            'url' => $link,
            'tag' => 'cetsy-message-' . $receiver->id,
            'icon' => (string) config('webpush.icon'),
            'badge' => (string) config('webpush.badge'),
            'message' => [
                'id' => (int) $message->id,
                'sender_name' => $senderName,
                'body_preview' => $body,
                'link' => $link,
                'created_at' => optional($message->created_at)->toIso8601String(),
            ],
        ]);
    }

    public function sendToUser(User $user, array $payload): int
    {
        if (! $this->isConfigured()) {
            return 0;
        }

        /** @var Collection<int, PushSubscriptionModel> $subscriptions */
        $subscriptions = $user->pushSubscriptions()->get();
        if ($subscriptions->isEmpty()) {
            return 0;
        }

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => (string) config('webpush.vapid.subject'),
                'publicKey' => (string) config('webpush.vapid.public_key'),
                'privateKey' => (string) config('webpush.vapid.private_key'),
            ],
        ]);
        $webPush->setReuseVAPIDHeaders(true);

        $payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (! is_string($payloadJson)) {
            return 0;
        }

        foreach ($subscriptions as $subscription) {
            $webPush->queueNotification(
                WebPushSubscription::create([
                    'endpoint' => $subscription->endpoint,
                    'publicKey' => $subscription->public_key,
                    'authToken' => $subscription->auth_token,
                    'contentEncoding' => $subscription->content_encoding ?: 'aes128gcm',
                ]),
                $payloadJson
            );
        }

        $sentCount = 0;
        $subscriptionsByHash = $subscriptions->keyBy('endpoint_hash');

        try {
            foreach ($webPush->flush() as $report) {
                $subscription = $subscriptionsByHash->get(hash('sha256', $report->getEndpoint()));

                if ($report->isSuccess()) {
                    $sentCount++;
                    if ($subscription) {
                        $subscription->forceFill(['last_used_at' => now()])->saveQuietly();
                    }
                    continue;
                }

                Log::warning('Web push delivery failed.', [
                    'user_id' => $user->id,
                    'endpoint' => $report->getEndpoint(),
                    'reason' => $report->getReason(),
                ]);

                if ($subscription && $report->isSubscriptionExpired()) {
                    $subscription->delete();
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Web push dispatch crashed.', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);
        }

        return $sentCount;
    }
}
