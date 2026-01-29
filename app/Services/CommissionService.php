<?php

namespace App\Services;

use App\Models\Wallet;

class CommissionService
{
    public static function percent(): float
    {
        $raw = function_exists('setting') ? setting('release_fee_percent', env('HOLD_RELEASE_FEE_PERCENT', 5.5)) : env('HOLD_RELEASE_FEE_PERCENT', 5.5);
        $pct = is_numeric($raw) ? (float) $raw : 0.0;
        return max(0.0, $pct);
    }

    public static function amount(float $gross): float
    {
        $pct = self::percent();
        if ($pct <= 0 || $gross <= 0) {
            return 0.0;
        }
        return round($gross * $pct / 100, 2);
    }

    public static function commissionRow(int $userId, int $orderId): ?Wallet
    {
        return Wallet::where('user_id', $userId)
            ->where(function ($q) {
                $q->where('method', 'platform_fee')
                  ->orWhere('type', 'transaction_fee');
            })
            ->where('meta->order_id', $orderId)
            ->orderByDesc('id')
            ->first();
    }

    public static function commissionExists(int $userId, int $orderId): bool
    {
        return (bool) Wallet::where('user_id', $userId)
            ->where(function ($q) {
                $q->where('method', 'platform_fee')
                  ->orWhere('type', 'transaction_fee');
            })
            ->where('meta->order_id', $orderId)
            ->exists();
    }

    public static function createCommissionRow(int $userId, int $orderId, float $gross, string $status = 'on_hold'): ?Wallet
    {
        if (self::commissionExists($userId, $orderId)) {
            return null;
        }
        $amount = self::amount($gross);
        if ($amount <= 0) {
            return null;
        }
        $pct = self::percent();

        return Wallet::create([
            'user_id'     => $userId,
            'credit'      => 0,
            'debit'       => $amount,
            'balance'     => 0,
            'type'        => 'transaction_fee',
            'method'      => 'platform_fee',
            'reference'   => 'FEE-ORDER-' . $orderId . '-' . strtoupper(uniqid()),
            'description' => 'Platform commission ' . rtrim(rtrim(number_format($pct, 2), '0'), '.') . '% for Order #' . $orderId,
            'status'      => $status,
            'meta'        => [
                'order_id' => $orderId,
                'percent'  => $pct,
                'gross'    => $gross,
                'amount'   => $amount,
            ],
        ]);
    }

    public static function refundCommission(int $userId, int $orderId, float $refundAmount, float $orderTotal, string $status = 'on_hold'): ?Wallet
    {
        if ($refundAmount <= 0 || $orderTotal <= 0) {
            return null;
        }

        $row = self::commissionRow($userId, $orderId);
        if (!$row) {
            return null;
        }

        $commissionAmount = (float) ($row->debit ?? 0);
        if ($commissionAmount <= 0) {
            return null;
        }

        $ratio = min(1, max(0, $refundAmount / $orderTotal));
        $refund = round($commissionAmount * $ratio, 2);
        if ($refund <= 0) {
            return null;
        }

        return Wallet::create([
            'user_id'     => $userId,
            'credit'      => $refund,
            'debit'       => 0,
            'balance'     => 0,
            'type'        => 'transaction_fee_refund',
            'method'      => 'platform_fee_refund',
            'reference'   => 'FEE-REFUND-' . $orderId . '-' . strtoupper(uniqid()),
            'description' => 'Platform fee refund for Order #' . $orderId,
            'status'      => $status,
            'meta'        => [
                'order_id'        => $orderId,
                'refund_amount'   => $refund,
                'refund_ratio'    => $ratio,
                'commission_debit'=> (float) $commissionAmount,
            ],
        ]);
    }
}
