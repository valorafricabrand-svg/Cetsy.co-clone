<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Order;
use App\Models\Wallet;
use App\Models\Payment;
use App\Services\CommissionService;

class ReleaseOnHoldFunds extends Command
{
    protected $signature = 'orders:release-onhold {--days= : Days after shipped to auto-release (defaults to settings/3)}';
    protected $description = 'Auto-release on-hold seller funds for shipped orders after a grace period and mark orders completed.';

    public function handle(): int
    {
        $defaultDays = function_exists('setting') ? (int) setting('auto_release_days', 3) : 3;
        $daysOption  = $this->option('days');
        $days        = is_numeric($daysOption) ? (int) $daysOption : $defaultDays;

        $cutoff = now()->subDays(max(1, $days));

        $orders = Order::where('status', Order::STATUS_SHIPPED)
            ->where('updated_at', '<=', $cutoff)
            ->with([
                'shop:id,user_id,name',
                'shop.user:id,name,email',
                'user:id,name,email',
                'payments:id,order_id,local_transaction_id'
            ])
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No shipped orders eligible for auto-release.');
            return 0;
        }

        $released = 0;

        foreach ($orders as $order) {
            $justReleased = false;
            DB::transaction(function () use ($order, &$released, &$justReleased) {
                // Mark order completed and delivered_at if not set
                $order->update([
                    'status'       => Order::STATUS_COMPLETED,
                    'delivered_at' => $order->delivered_at ?: now(),
                ]);

                $sellerId = optional($order->shop)->user_id;
                if (!$sellerId) {
                    return; // skip if shop/user missing
                }

                // Try to match on-hold wallet rows by meta->order_id or fallback by payment reference
                $paymentRef = optional($order->payments->first())->local_transaction_id;

                $rows = Wallet::where('user_id', $sellerId)
                    ->where('status', 'on_hold')
                    ->where(function ($q) use ($order, $paymentRef) {
                        $q->where('meta->order_id', $order->id);
                        if ($paymentRef) {
                            $q->orWhere('reference', $paymentRef);
                        }
                    })
                    ->get();

                if ($rows->isNotEmpty()) {
                    $hasFee = CommissionService::commissionExists($sellerId, (int) $order->id);
                    $percent = CommissionService::percent();
                    foreach ($rows as $row) {
                        $amount = (float) (($row->credit ?? 0) - ($row->debit ?? 0));
                        $fee = round(max(0, $amount) * max(0, $percent) / 100, 2);
                        if (!$hasFee && $fee > 0.0) {
                            Wallet::create([
                                'user_id'     => $sellerId,
                                'credit'      => 0,
                                'debit'       => $fee,
                                'balance'     => 0,
                                'type'        => 'transaction_fee',
                                'method'      => 'platform_fee',
                                'reference'   => 'FEE-'.$row->id,
                                'description' => 'Transaction fee '.$percent.'% for Order #'.$order->id,
                                'status'      => 'completed',
                                'meta'        => [ 'source_wallet_id' => $row->id, 'order_id' => $order->id, 'percent' => $percent ],
                            ]);
                        }
                        $row->status = 'completed';
                        $row->save();
                        $released++;
                        $justReleased = true;
                    }
                }
            });

            // Send notifications if any wallet rows were released for this order
            if ($justReleased) {
                try {
                    $order->loadMissing(['shop.user', 'user']);
                    $buyer  = $order->user;
                    $seller = optional($order->shop)->user;

                    if ($seller && !empty($seller->email)) {
                        Mail::to($seller->email)->send(
                            new \App\Mail\OrderAutoReleasedSellerMail($order, $seller, $buyer, $order->shop)
                        );
                    }
                    if ($buyer && !empty($buyer->email)) {
                        Mail::to($buyer->email)->send(
                            new \App\Mail\OrderAutoReleasedBuyerMail($order, $buyer, $order->shop, $seller)
                        );
                    }
                } catch (\Throwable $e) {
                    Log::error('Failed to send auto-release emails: '.$e->getMessage(), [
                        'order_id' => $order->id,
                    ]);
                }
            }
        }

        $this->info("Processed {$orders->count()} orders; released {$released} wallet rows.");
        Log::info('orders:release-onhold summary', ['orders' => $orders->count(), 'released_rows' => $released, 'days' => $days]);
        return 0;
    }
}
