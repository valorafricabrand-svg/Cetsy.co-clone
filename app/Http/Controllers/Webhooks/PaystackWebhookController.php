<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaystackWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $raw = $request->getContent();
        $signature = (string) $request->header('x-paystack-signature');

        $secret = (string) (config('services.paystack.secret') ?: (function_exists('setting') ? (setting('paystack_secret') ?? '') : ''));
        if ($secret === '') {
            Log::warning('paystack.webhook.missing_secret');
            return response()->json(['ok' => false], 400);
        }

        $computed = hash_hmac('sha512', $raw, $secret);
        if (!hash_equals($computed, $signature)) {
            Log::warning('paystack.webhook.invalid_signature');
            return response()->json(['ok' => false], 400);
        }

        $payload = $request->json()->all();
        $event = (string) ($payload['event'] ?? '');
        $data = $payload['data'] ?? [];

        if ($event !== 'charge.success') {
            return response()->json(['ok' => true]);
        }

        $reference = (string) ($data['reference'] ?? '');
        if ($reference === '') {
            Log::warning('paystack.webhook.missing_reference');
            return response()->json(['ok' => false], 400);
        }

        $marker = Wallet::where('method', 'paystack')
            ->where('external_id', $reference)
            ->latest('id')
            ->first();

        if (!$marker) {
            Log::info('paystack.webhook.marker_not_found', ['reference' => $reference]);
            return response()->json(['ok' => true]);
        }

        // Idempotency: if already credited, no-op
        $alreadyCredited = Wallet::where('user_id', $marker->user_id)
            ->where('method', 'paystack')
            ->where('external_id', $reference)
            ->where('credit', '>', 0)
            ->exists();
        if ($alreadyCredited || $marker->status === 'completed') {
            return response()->json(['ok' => true]);
        }

        $currency = strtoupper((string) ($data['currency'] ?? data_get($marker->meta, 'currency', 'USD')));
        $amountTotal = (int) ($data['amount'] ?? 0);
        $expectedCurrency = strtoupper((string) data_get($marker->meta, 'currency', $currency));
        $expectedTotal = (int) data_get($marker->meta, 'amount_total', $amountTotal);
        $credit = (float) data_get($marker->meta, 'credit', 0);

        if ($expectedCurrency !== $currency || $expectedTotal !== $amountTotal) {
            Log::warning('paystack.webhook.amount_mismatch', [
                'reference' => $reference,
                'expected'  => ['currency' => $expectedCurrency, 'amount_total' => $expectedTotal],
                'actual'    => ['currency' => $currency, 'amount_total' => $amountTotal],
            ]);
            return response()->json(['ok' => false], 400);
        }

        $purpose = (string) data_get($marker->meta, 'purpose', 'wallet_deposit');

        $walletRow = null;
        try {
            $walletRow = Wallet::create([
                'user_id'     => (int) $marker->user_id,
                'credit'      => (float) $credit,
                'debit'       => 0,
                'balance'     => 0,
                'reference'   => strtoupper(uniqid('TXN-')),
                'method'      => 'paystack',
                'description' => $purpose === 'order_topup' ? 'Order top-up via Paystack' : 'Deposit via Paystack',
                'external_id' => $reference,
                'status'      => 'completed',
                'meta'        => ['paystack' => $data, 'order_id' => (int) data_get($marker->meta, 'order_id')],
            ]);

            $marker->status = 'completed';
            $marker->description = trim(($marker->description ?? '') . ' | Success');
            $marker->save();

            $activityMsg = $purpose === 'order_topup'
                ? 'Your Paystack order top-up of $' . number_format((float) $credit, 2) . ' was received'
                : 'You made a deposit of $' . number_format((float) $credit, 2);
            Activity::create([
                'user_id'     => (int) $marker->user_id,
                'is_read'     => false,
                'description' => $activityMsg,
            ]);
        } catch (\Throwable $e) {
            Log::error('paystack.webhook.finalize_failed', ['reference' => $reference, 'error' => $e->getMessage()]);
            return response()->json(['ok' => false], 500);
        }

        // Optional: notify user on wallet deposit
        if ($purpose === 'wallet_deposit') {
            try {
                $user = \App\Models\User::find($marker->user_id);
                if ($user && $walletRow) {
                    Mail::to($user->email)->send(new \App\Mail\WalletDepositSuccessMail(
                        $user,
                        $walletRow,
                        (float) $credit,
                        $walletRow->reference
                    ));
                }
            } catch (\Throwable $e) {
                Log::warning('paystack.webhook.email_failed', ['user_id' => $marker->user_id, 'error' => $e->getMessage()]);
            }
        }

        // If this was an order top-up, leave the order payable (buyer can complete using wallet)
        return response()->json(['ok' => true]);
    }
}
