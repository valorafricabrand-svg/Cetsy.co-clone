<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use App\Models\Order;

class AutoCancelPendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:auto-cancel {--hours=24 : Cancel orders older than this many hours}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically cancel orders that remain pending beyond a time threshold (default 24h).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours  = (int) $this->option('hours');
        $cutoff = now()->subHours(max(1, $hours));

        $this->info(sprintf('Auto-cancelling pending orders created on/before %s', $cutoff->toDateTimeString()));

        $query = Order::query()
            ->where('status', Order::STATUS_PENDING)
            ->where('created_at', '<=', $cutoff);

        $total = 0;
        $query->orderBy('id')
            ->chunkById(100, function ($orders) use (&$total) {
                foreach ($orders as $order) {
                    try {
                        // Skip if paid (defensive; typically paid orders are not pending)
                        if (method_exists($order, 'isPaid') && $order->isPaid()) {
                            continue;
                        }

                        $reason = 'Automatically cancelled after 24 hours without payment.';
                        $order->status = Order::STATUS_CANCELLED;
                        if (array_key_exists('cancel_reason', $order->getAttributes()) || $order->isFillable('cancel_reason')) {
                            $order->cancel_reason = $reason;
                        }
                        $order->save();

                        // Send notifications (best-effort)
                        try {
                            $order->load(['shop.user', 'user', 'items.product']);
                            $buyer     = $order->user;
                            $shop      = $order->shop;
                            $shopOwner = optional($shop)->user;

                            if ($buyer && $buyer->email) {
                                Mail::to($buyer->email)->send(
                                    new \App\Mail\OrderCancelledBuyerMail($order, $buyer, $shop, $reason)
                                );
                            }
                            if ($shopOwner && $shopOwner->email) {
                                Mail::to($shopOwner->email)->send(
                                    new \App\Mail\OrderCancelledShopOwnerMail($order, $shopOwner, $buyer, $shop, $reason)
                                );
                            }
                        } catch (\Throwable $e) {
                            Log::error('orders.auto_cancel.email_failed', [
                                'order_id' => $order->id,
                                'error'    => $e->getMessage(),
                            ]);
                        }

                        $total++;
                    } catch (\Throwable $e) {
                        Log::error('orders.auto_cancel.failed', [
                            'order_id' => $order->id,
                            'error'    => $e->getMessage(),
                        ]);
                    }
                }
            });

        $this->info("Auto-cancelled {$total} orders.");
        return Command::SUCCESS;
    }
}

