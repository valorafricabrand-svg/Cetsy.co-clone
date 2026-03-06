<?php

namespace App\Http\Controllers;

use App\Models\DigitalFile;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Wallet;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class DigitalFileController extends Controller
{
    public function destroy(DigitalFile $digitalFile)
    {
        $user = request()->user();
        $ownerId = (int) optional(optional($digitalFile->product)->shop)->user_id;
        $isAdmin = $user && (method_exists($user, 'isAdmin') ? $user->isAdmin() : (bool) ($user->is_admin ?? false));

        abort_unless($user && ($isAdmin || (int) $user->id === $ownerId), 403);

        $digitalFile->deleteStoredAsset();
        $digitalFile->delete();

        return redirect()->back()->with('success', 'Digital file deleted successfully.');
    }

    public function download(Request $request, DigitalFile $file)
    {
        $user = $request->user();
        abort_unless($user, 403);
        $isAdmin = $user && (method_exists($user, 'isAdmin') ? $user->isAdmin() : (bool) ($user->is_admin ?? false));
        $isOwner = $user && (int) optional(optional($file->product)->shop)->user_id === (int) $user->id;

        $ownsFile = Order::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [Order::STATUS_PROCESSING, Order::STATUS_COMPLETED, Order::STATUS_DELIVERED])
            ->whereHas('items', function ($q) use ($file) {
                $q->where('product_id', $file->product_id);
            })
            ->exists();

        if (! $ownsFile && ! $isAdmin && ! $isOwner) {
            abort(403, 'Not authorised to download this file.');
        }

        if ($ownsFile) {
            $this->markDownloadState($user, $file);
            $this->releaseDigitalOrderFunds($user, $file);
        }

        if ($file->isExternalUrl()) {
            abort_if(empty($file->external_url), 404, 'File not found.');

            return redirect()->away($file->external_url);
        }

        $path = $file->filepath;
        $disk = Storage::disk($file->resolvedDisk());

        if (! $path || ! $disk->exists($path)) {
            abort(404, 'File not found.');
        }

        return $disk->download($path, $file->filename);
    }

    private function markDownloadState($user, DigitalFile $file): void
    {
        try {
            OrderItem::where('product_id', $file->product_id)
                ->whereHas('order', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->whereIn('status', [
                            Order::STATUS_PROCESSING,
                            Order::STATUS_COMPLETED,
                            Order::STATUS_DELIVERED,
                        ]);
                })
                ->get()
                ->each(function ($item) {
                    $item->download_count = (int) $item->download_count + 1;
                    if (empty($item->downloaded_at)) {
                        $item->downloaded_at = now();
                    }
                    $item->save();
                });
        } catch (\Throwable $e) {
            Log::warning('Failed to mark digital download', [
                'file_id' => $file->id,
                'product_id' => $file->product_id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function releaseDigitalOrderFunds($user, DigitalFile $file): void
    {
        try {
            $order = Order::query()
                ->where('user_id', $user->id)
                ->whereIn('status', [Order::STATUS_PROCESSING, Order::STATUS_SHIPPED])
                ->whereHas('items', function ($q) use ($file) {
                    $q->where('product_id', $file->product_id);
                })
                ->with(['items.product', 'shop.user', 'user'])
                ->latest('id')
                ->first();

            if (! $order) {
                return;
            }

            $isDigitalOnly = $order->items->every(function ($it) {
                return optional($it->product)->type === 'digital';
            });

            if (! $isDigitalOnly || $order->status === Order::STATUS_COMPLETED) {
                return;
            }

            DB::transaction(function () use ($order) {
                $order->update([
                    'status'       => Order::STATUS_COMPLETED,
                    'delivered_at' => $order->delivered_at ?: now(),
                ]);

                $sellerId = optional($order->shop)->user_id;
                if (! $sellerId) {
                    return;
                }

                $rows = Wallet::where('user_id', $sellerId)
                    ->where('status', 'on_hold')
                    ->where(function ($q) use ($order) {
                        $q->where('meta->order_id', $order->id)
                            ->orWhere('reference', function ($sub) use ($order) {
                                $sub->select('local_transaction_id')
                                    ->from((new Payment)->getTable())
                                    ->where('order_id', $order->id)
                                    ->limit(1);
                            });
                    })
                    ->get();

                if ($rows->isEmpty()) {
                    return;
                }

                $hasFee = CommissionService::commissionExists($sellerId, (int) $order->id);
                $percent = CommissionService::percent();

                foreach ($rows as $row) {
                    $amount = (float) (($row->credit ?? 0) - ($row->debit ?? 0));
                    $fee = round(max(0, $amount) * max(0, $percent) / 100, 2);

                    if (! $hasFee && $fee > 0.0) {
                        Wallet::create([
                            'user_id'     => $sellerId,
                            'credit'      => 0,
                            'debit'       => $fee,
                            'balance'     => 0,
                            'type'        => 'transaction_fee',
                            'method'      => 'platform_fee',
                            'reference'   => 'FEE-' . $row->id,
                            'description' => 'Transaction fee ' . $percent . '% for Order #' . $order->id,
                            'status'      => 'completed',
                            'meta'        => [
                                'source_wallet_id' => $row->id,
                                'order_id' => $order->id,
                                'percent' => $percent,
                            ],
                        ]);
                    }

                    $row->status = 'completed';
                    $row->save();
                }
            });

            try {
                $order->loadMissing(['shop.user', 'user']);
                $buyer = $order->user;
                $seller = optional($order->shop)->user;

                if ($seller && ! empty($seller->email)) {
                    Mail::to($seller->email)->send(
                        new \App\Mail\DigitalOrderCompletedSellerMail($order, $seller, $buyer, $order->shop)
                    );
                }

                if ($buyer && ! empty($buyer->email)) {
                    Mail::to($buyer->email)->send(
                        new \App\Mail\DigitalOrderCompletedBuyerMail($order, $buyer, $order->shop, $seller)
                    );
                }
            } catch (\Throwable $e) {
                Log::error('Failed to send digital-complete emails: ' . $e->getMessage(), [
                    'order_id' => $order->id,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Digital order auto-release failed', [
                'file_id'    => $file->id,
                'product_id' => $file->product_id,
                'user_id'    => $user->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
