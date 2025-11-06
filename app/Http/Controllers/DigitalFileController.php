<?php

namespace App\Http\Controllers;

use App\Models\DigitalFile;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Wallet;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class DigitalFileController extends Controller
{
    /**
     * Remove the specified digital file from storage.
     *
     * @param  \App\Models\DigitalFile  $digitalFile
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(DigitalFile $digitalFile)
    {
        // Delete the physical file from storage
        if (Storage::exists($digitalFile->filepath)) {
            Storage::delete($digitalFile->filepath);
        }

        // Delete the database record
        $digitalFile->delete();

        return redirect()->back()->with('success', 'Digital file deleted successfully.');
    }


    public function download(Request $request, DigitalFile $file)
    {
        $user = $request->user();

        // ─── Authorise ───────────────────────────────────────────────
        // Customer must have an order (processing or completed) that
        // contains a product linked to this file.  Adjust as needed.
        $ownsFile = Order::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [Order::STATUS_PROCESSING, Order::STATUS_COMPLETED])
            ->whereHas('items', function ($q) use ($file) {
                $q->where('product_id', $file->product_id);
            })
            ->exists();

        if (! $ownsFile && ! $user->is_admin) {
            abort(403, 'Not authorised to download this file.');
        }

        // ─── Serve the file ──────────────────────────────────────────
        $disk     = Storage::disk($file->disk ?? 'private'); // e.g. "private"
        $path     = $file->filepath;                         // stored path
        $filename = $file->filename;                         // nice name

        if (! $disk->exists($path)) {
            abort(404, 'File not found.');
        }

        // Mark download confirmation on related order items for this user/product
        try {
            OrderItem::where('product_id', $file->product_id)
                ->whereHas('order', function($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->whereIn('status', [\App\Models\Order::STATUS_PROCESSING, \App\Models\Order::STATUS_COMPLETED, \App\Models\Order::STATUS_DELIVERED]);
                })
                ->get()
                ->each(function($item){
                    $item->download_count = (int) $item->download_count + 1;
                    if (empty($item->downloaded_at)) {
                        $item->downloaded_at = now();
                    }
                    $item->save();
                });
        } catch (\Throwable $e) {
            // do not block download on logging failure
            Log::warning('Failed to mark digital download', [
                'file_id' => $file->id,
                'product_id' => $file->product_id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        // If this is a digital-only order, release seller funds on first download and mark order completed
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

            if ($order) {
                $isDigitalOnly = $order->items->every(function($it){
                    return optional($it->product)->type === 'digital';
                });

                if ($isDigitalOnly && $order->status !== Order::STATUS_COMPLETED) {
                    DB::transaction(function () use ($order) {
                        // Mark order completed
                        $order->update([
                            'status'       => Order::STATUS_COMPLETED,
                            'delivered_at' => $order->delivered_at ?: now(),
                        ]);

                        // Release seller funds that were on hold for this order (apply fee)
                        $sellerId = optional($order->shop)->user_id;
                        if ($sellerId) {
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
                            if ($rows->isNotEmpty()) {
                                $percent = (float) (function_exists('setting') ? setting('release_fee_percent', env('HOLD_RELEASE_FEE_PERCENT', 5.5)) : env('HOLD_RELEASE_FEE_PERCENT', 5.5));
                                foreach ($rows as $row) {
                                    $amount = (float) (($row->credit ?? 0) - ($row->debit ?? 0));
                                    $fee = round(max(0, $amount) * max(0, $percent) / 100, 2);
                                    if ($fee > 0.0) {
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
                                }
                            }
                        }
                    });

                    // Notify both parties with dedicated digital-complete emails
                    try {
                        $order->loadMissing(['shop.user', 'user']);
                        $buyer  = $order->user;
                        $seller = optional($order->shop)->user;

                        if ($seller && !empty($seller->email)) {
                            Mail::to($seller->email)->send(
                                new \App\Mail\DigitalOrderCompletedSellerMail($order, $seller, $buyer, $order->shop)
                            );
                        }
                        if ($buyer && !empty($buyer->email)) {
                            Mail::to($buyer->email)->send(
                                new \App\Mail\DigitalOrderCompletedBuyerMail($order, $buyer, $order->shop, $seller)
                            );
                        }
                    } catch (\Throwable $e) {
                        Log::error('Failed to send digital-complete emails: '.$e->getMessage(), [
                            'order_id' => $order->id,
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('Digital order auto-release failed', [
                'file_id'    => $file->id,
                'product_id' => $file->product_id,
                'user_id'    => $user->id,
                'error'      => $e->getMessage(),
            ]);
        }

        // Force-download so it doesn't open in browser
        return $disk->download($path, $filename);
    }
}
