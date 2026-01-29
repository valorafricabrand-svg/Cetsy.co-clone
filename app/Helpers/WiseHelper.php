<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WiseHelper
{
    public static function createPayout(string $recipientId, float $amount, string $currency, string $note, string $reference, ?string $profileId = null): array
    {
        $token = (string) config('services.wise.token');
        $profileId = $profileId ?: (string) config('services.wise.profile_id');
        if ($token === '' || $profileId === '') {
            return ['status' => 'error', 'message' => 'Wise API is not configured.'];
        }
        if ($recipientId === '') {
            return ['status' => 'error', 'message' => 'Missing Wise recipient ID.'];
        }
        if ($amount <= 0) {
            return ['status' => 'error', 'message' => 'Invalid payout amount.'];
        }

        $base = 'https://api.transferwise.com';

        try {
            // 1) Create quote
            $quoteResp = Http::withToken($token)
                ->post($base . '/v2/quotes', [
                    'profile' => (int) $profileId,
                    'sourceCurrency' => strtoupper($currency),
                    'targetCurrency' => strtoupper($currency),
                    'targetAmount' => $amount,
                ]);

            if (!$quoteResp->successful()) {
                Log::error('Wise quote failed', ['status' => $quoteResp->status(), 'body' => $quoteResp->body()]);
                return ['status' => 'error', 'message' => 'Wise quote failed'];
            }

            $quoteId = $quoteResp->json('id');
            if (!$quoteId) {
                return ['status' => 'error', 'message' => 'Wise quote ID missing'];
            }

            // 2) Create transfer
            $transferResp = Http::withToken($token)
                ->post($base . '/v1/transfers', [
                    'targetAccount' => $recipientId,
                    'quote' => $quoteId,
                    'customerTransactionId' => $reference,
                    'details' => [
                        'reference' => $note,
                    ],
                ]);

            if (!$transferResp->successful()) {
                Log::error('Wise transfer failed', ['status' => $transferResp->status(), 'body' => $transferResp->body()]);
                return ['status' => 'error', 'message' => 'Wise transfer failed'];
            }

            $transferId = $transferResp->json('id');
            if (!$transferId) {
                return ['status' => 'error', 'message' => 'Wise transfer ID missing'];
            }

            // 3) Fund transfer
            $fundResp = Http::withToken($token)
                ->post($base . '/v3/profiles/' . (int) $profileId . '/transfers/' . (int) $transferId . '/payments', [
                    'type' => 'BALANCE',
                ]);

            if (!$fundResp->successful()) {
                Log::error('Wise fund failed', ['status' => $fundResp->status(), 'body' => $fundResp->body()]);
                return ['status' => 'error', 'message' => 'Wise funding failed'];
            }

            return [
                'status' => 'success',
                'data' => [
                    'quote' => $quoteResp->json(),
                    'transfer' => $transferResp->json(),
                    'fund' => $fundResp->json(),
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('Wise payout exception', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
