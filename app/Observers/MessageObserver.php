<?php

namespace App\Observers;

use App\Models\Message;
use App\Services\WebPushService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MessageObserver
{
    public function created(Message $message): void
    {
        if (! $message->receiver_id) {
            return;
        }

        $dispatch = function () use ($message): void {
            try {
                $freshMessage = Message::query()->find($message->getKey());
                if (! $freshMessage) {
                    return;
                }

                app(WebPushService::class)->sendMessage($freshMessage);
            } catch (\Throwable $e) {
                Log::warning('Failed to deliver message web push.', [
                    'message_id' => $message->id,
                    'error' => $e->getMessage(),
                ]);
            }
        };

        if (! app()->runningUnitTests() && DB::transactionLevel() > 0) {
            DB::afterCommit($dispatch);
            return;
        }

        $dispatch();
    }
}
