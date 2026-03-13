<?php

namespace App\Observers;

use App\Models\Activity;
use App\Services\WebPushService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActivityObserver
{
    public function created(Activity $activity): void
    {
        if (! $activity->user_id || $activity->is_read || $activity->type === Activity::TYPE_MESSAGE) {
            return;
        }

        $dispatch = function () use ($activity): void {
            try {
                $freshActivity = Activity::query()->find($activity->getKey());
                if (! $freshActivity) {
                    return;
                }

                app(WebPushService::class)->sendActivity($freshActivity);
            } catch (\Throwable $e) {
                Log::warning('Failed to deliver activity web push.', [
                    'activity_id' => $activity->id,
                    'message' => $e->getMessage(),
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
