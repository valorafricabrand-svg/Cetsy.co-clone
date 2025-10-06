<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\Activity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubscriptionExpiryReminderMail;

class SendSubscriptionExpiryReminders extends Command
{
    protected $signature = 'subscriptions:remind-expiring {--days=* : Override reminder day offsets (e.g. --days=30 --days=7 --days=1)}';

    protected $description = 'Send subscription expiry reminders at configured day offsets (e.g. 30, 7, 1 days before end date).';

    public function handle(): int
    {
        $configured = config('subscription.reminder_days', [30, 7, 1]);
        $offsets = collect($this->option('days'))
            ->map(fn($d) => (int)$d)
            ->filter(fn($d) => $d > 0)
            ->values();
        if ($offsets->isEmpty()) {
            $offsets = collect($configured);
        }

        $count = 0; $fail = 0;
        foreach ($offsets->unique()->sortDesc() as $days) {
            $targetDate = now()->addDays($days)->toDateString();

            $subs = Subscription::query()
                ->where('status', 'active')
                ->whereDate('end_date', '=', $targetDate)
                ->with('user:id,name,email')
                ->get();

            foreach ($subs as $sub) {
                $user = $sub->user;
                if (!$user || empty($user->email)) {
                    $fail++; continue;
                }

                try {
                    Mail::to($user->email)->send(new SubscriptionExpiryReminderMail($sub, $days));
                    $count++;

                    // Create an in-app activity
                    Activity::create([
                        'user_id' => $user->id,
                        'is_read' => false,
                        'description' => 'Your subscription will expire in ' . $days . ' day' . ($days === 1 ? '' : 's') . ' on ' . $sub->end_date->format('M d, Y'),
                        'type' => Activity::TYPE_SUBSCRIPTION,
                        'related_id' => $sub->id,
                        'related_type' => 'subscription',
                        'link' => route('seller.subscription'),
                    ]);
                } catch (\Throwable $e) {
                    $fail++;
                    $this->warn('Failed for user ' . $user->id . ': ' . $e->getMessage());
                }
            }
        }

        $this->info("Reminders sent: {$count}. Failures: {$fail}.");
        return self::SUCCESS;
    }
}

