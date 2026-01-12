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
    public static function trialEnabled(): bool
    {
        $default = (bool) env('SUBSCRIPTION_TRIAL_ENABLED', true);
        if (!function_exists('setting')) {
            return $default;
        }

        $raw = setting('subscription_trial_enabled', $default);
        if (is_bool($raw)) {
            return $raw;
        }
        if (is_numeric($raw)) {
            return ((int) $raw) === 1;
        }

        $raw = strtolower(trim((string) $raw));
        if ($raw === '') {
            return $default;
        }

        return in_array($raw, ['1', 'true', 'yes', 'on', 'enabled', 'active'], true);
    }

    public static function trialDays(): int
    {
        $default = (int) env('SUBSCRIPTION_TRIAL_DAYS', 30);
        $raw = function_exists('setting')
            ? setting('subscription_trial_days', $default)
            : $default;

        $days = (int) $raw;
        if ($days <= 0) {
            $days = $default > 0 ? $default : 30;
        }

        return min(365, $days);
    }

    public static function startTrialIfEligible(User $user): ?Subscription
    {
        if (!method_exists($user, 'isSeller') || !$user->isSeller()) {
            return null;
        }

        if (!self::trialEnabled()) {
            return null;
        }

        $days = self::trialDays();
        if ($days <= 0) {
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
            'notes'          => 'Free ' . $days . '-day trial',
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
            'description'  => 'You started your free ' . $days . '-day seller trial',
            'type'         => Activity::TYPE_SUBSCRIPTION,
            'related_id'   => $subscription->id,
            'related_type' => 'subscription',
        ]);

        return $subscription;
    }
}
