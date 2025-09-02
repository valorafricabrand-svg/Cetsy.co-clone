<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalHelper
{
    /**
     * Get OAuth token from PayPal.
     */
    public static function getAccessToken(): ?string
    {
        $client = env('PAYPAL_CLIENT_ID') ?: (function_exists('setting') ? setting('paypal_client_id') : null);
        $secret = env('PAYPAL_SECRET');
        $mode   = env('PAYPAL_MODE', 'sandbox');

        if (!$client || !$secret) {
            Log::error('PayPal credentials missing. Ensure PAYPAL_CLIENT_ID and PAYPAL_SECRET are configured.');
            return null;
        }

        $base = $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

        try {
            $response = Http::asForm()
                ->withBasicAuth($client, $secret)
                ->post($base . '/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ]);

            if (!$response->successful()) {
                Log::error('PayPal OAuth failed', ['body' => $response->body()]);
                return null;
            }

            return $response->json()['access_token'] ?? null;
        } catch (\Throwable $e) {
            Log::error('PayPal OAuth exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Create a single-item Payout to an email.
     * Returns [status => success|error, message, data].
     */
    public static function createPayout(string $receiverEmail, float $amount, string $note, string $senderItemId): array
    {
        $mode = env('PAYPAL_MODE', 'sandbox');
        $base = $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
        $token = self::getAccessToken();
        if (!$token) {
            return ['status' => 'error', 'message' => 'Missing PayPal access token'];
        }

        $payload = [
            'sender_batch_header' => [
                'sender_batch_id' => uniqid('batch_'),
                'email_subject'   => 'You have a payout',
                'email_message'   => 'You have received a payout from ' . (config('app.name') ?? 'our marketplace'),
            ],
            'items' => [[
                'recipient_type' => 'EMAIL',
                'amount' => [
                    'value'    => number_format($amount, 2, '.', ''),
                    'currency' => 'USD', // Assuming USD wallet; adapt if multi-currency support
                ],
                'receiver'      => $receiverEmail,
                'note'          => $note,
                'sender_item_id'=> $senderItemId,
            ]],
        ];

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->post($base . '/v1/payments/payouts', $payload);

            $data = $response->json();
            if ($response->successful()) {
                return ['status' => 'success', 'message' => 'Payout created', 'data' => $data];
            }
            Log::error('PayPal payout failed', ['status' => $response->status(), 'data' => $data]);
            return ['status' => 'error', 'message' => $data['message'] ?? 'Payout failed', 'data' => $data];
        } catch (\Throwable $e) {
            Log::error('PayPal payout exception', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}

