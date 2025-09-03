<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\PayoutRequest;
use App\Helpers\PayPalHelper;
use Illuminate\Support\Facades\Mail;
use App\Mail\PayoutPaidMail;

class PayoutWebhookController extends Controller
{
    // PayPal Payouts webhook endpoint
    public function paypal(Request $request)
    {
        $raw = $request->getContent();
        $payload = $request->json()->all();
        $headers = $request->headers->all();

        // Robust verification via PayPal API
        if (!PayPalHelper::verifyWebhook($headers, $raw)) {
            Log::warning('PayPal webhook verification failed');
            return response()->json(['ok' => false], 400);
        }

        $eventType = $payload['event_type'] ?? '';
        $resource  = $payload['resource'] ?? [];
        $itemId    = $resource['sender_item_id'] ?? null; // e.g. payout_123

        if ($itemId && str_starts_with($itemId, 'payout_')) {
            $payoutId = (int) substr($itemId, 7);
            $payout   = PayoutRequest::find($payoutId);
            if ($payout) {
                $meta = $payout->meta ?? [];
                $meta['paypal_webhook'] = $payload;
                if (in_array($eventType, ['PAYMENT.PAYOUTS-ITEM.SUCCEEDED', 'PAYMENT.PAYOUTS-ITEM.COMPLETED'])) {
                    $meta['txn_reference'] = $meta['txn_reference'] ?? ($resource['payout_batch_id'] ?? ($resource['transaction_id'] ?? null));
                    $previous = $payout->status;
                    $payout->status  = 'paid';
                    $payout->paid_at = $payout->paid_at ?? now();
                }
                $payout->meta = $meta;
                $payout->save();

                // Notify if transitioned to paid
                if (($payout->wasChanged('status') && $payout->status === 'paid') || ($previous ?? null) !== 'paid') {
                    try {
                        $payout->loadMissing('user');
                        if ($payout->user && $payout->user->email) {
                            Mail::to($payout->user->email)->send(new PayoutPaidMail($payout, $payout->user));
                        }
                    } catch (\Throwable $e) { }
                }
            }
        }

        return response()->json(['ok' => true]);
    }

    // Daraja B2C result endpoint
    public function darajaB2CResult(Request $request)
    {
        $payload = $request->json()->all();
        $convId  = $payload['ConversationID'] ?? ($payload['Result']['ConversationID'] ?? null);
        $result  = $payload['Result'] ?? [];
        $code    = $result['ResultCode'] ?? null;

        if ($convId) {
            // Try locate payout by stored ConversationID in meta->mpesa
            $payout = PayoutRequest::where('meta->mpesa->ConversationID', $convId)->first();
            if ($payout) {
                $meta = $payout->meta ?? [];
                $meta['mpesa_result'] = $payload;
                if ((string) $code === '0') {
                    $previous = $payout->status;
                    $payout->status  = 'paid';
                    $payout->paid_at = $payout->paid_at ?? now();
                    // Optionally notify seller when transitioning to paid
                    if (($previous ?? null) !== 'paid') {
                        try {
                            $payout->loadMissing('user');
                            if ($payout->user && $payout->user->email) {
                                Mail::to($payout->user->email)->send(new PayoutPaidMail($payout, $payout->user));
                            }
                        } catch (\Throwable $e) { }
                    }
                }
                $payout->meta = $meta;
                $payout->save();
            } else {
                Log::warning('Daraja B2C result: payout not found for ConversationID', ['ConversationID' => $convId]);
            }
        }

        return response()->json(['ok' => true]);
    }

    // Daraja B2C timeout endpoint
    public function darajaB2CTimeout(Request $request)
    {
        $payload = $request->json()->all();
        Log::warning('Daraja B2C timeout', $payload);
        return response()->json(['ok' => true]);
    }
}
