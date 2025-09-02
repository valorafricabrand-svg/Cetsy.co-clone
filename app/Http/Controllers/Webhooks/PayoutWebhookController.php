<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\PayoutRequest;

class PayoutWebhookController extends Controller
{
    // PayPal Payouts webhook endpoint
    public function paypal(Request $request)
    {
        $payload = $request->json()->all();
        $headers = $request->headers->all();

        // Basic guard: ensure webhook id matches configured one if present
        $expectedId = env('PAYPAL_WEBHOOK_ID');
        $webhookId  = $payload['id'] ?? ($payload['webhook_id'] ?? null);
        if ($expectedId && $webhookId && $webhookId !== $expectedId) {
            Log::warning('PayPal webhook ID mismatch', ['expected' => $expectedId, 'got' => $webhookId]);
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
                    $payout->status  = 'paid';
                    $payout->paid_at = $payout->paid_at ?? now();
                }
                $payout->meta = $meta;
                $payout->save();
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
                    $payout->status  = 'paid';
                    $payout->paid_at = $payout->paid_at ?? now();
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

