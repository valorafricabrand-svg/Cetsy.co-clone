<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Payment;
use App\Models\Shop;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Str;

class SubscriptionService
{
    public static function startTrialIfEligible(User $user, int $days = 30): ?Subscription
    {
        if (!method_exists($user, 'isSeller') || !$user->isSeller()) {
            return null;
        }

        $hasAnySubscription = Subscription::where('user_id', $user->id)->exists();
        if ($hasAnySubscription) {
            return null;
        }

        $shop = $user->shop ?: Shop::where('user_id', $user->id)->first();
        $start = now();
        $end = (clone $start)->addDays($days);
        $transactionId = 'TRIAL-' . strtoupper(Str::random(10));

        $subscription = Subscription::create([
            'user_id'        => $user->id,
            'shop_id'        => $shop?->id,
            'status'         => 'active',
            'start_date'     => $start,
            'end_date'       => $end,
            'amount'         => 0,
            'payment_method' => 'trial',
            'transaction_id' => $transactionId,
            'notes'          => 'Free 30-day trial',
        ]);

        if ($shop) {
            $shop->is_active = true;
            $shop->save();
        }

        Payment::create([
            'user_id'              => $user->id,
            'shop_id'              => $shop?->id,
            'total_amount'         => 0,
            'payment_method'       => 'trial',
            'payment_status'       => 'successful',
            'paymentStatus'        => 3,
            'currency'             => 'USD',
            'local_transaction_id' => $transactionId,
            'payment_name'         => 'subscription_fee',
        ]);

        Activity::create([
            'user_id'      => $user->id,
            'is_read'      => false,
            'description'  => 'You started your free 30-day seller trial',
            'type'         => Activity::TYPE_SUBSCRIPTION,
            'related_id'   => $subscription->id,
            'related_type' => 'subscription',
        ]);

        return $subscription;
    }
}
