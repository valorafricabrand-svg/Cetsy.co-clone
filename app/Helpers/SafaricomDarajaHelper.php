<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class SafaricomDarajaHelper
{
    /**
     * Format a URL to ensure it starts with a valid protocol.
     *
     * @param string $url
     * @return string
     */

    private static function formatUrl($url)
    {
        if (!preg_match('/^https?:\/\//', $url)) {
            // Prepend https:// if no protocol is present
            return 'https://' . ltrim($url, '/');
        }
        return $url;
    } 

    /**
     * Get access token from Safaricom Daraja API.
     *
     * @return string|null
     */

    public static function getAccessToken()
    {
        $consumerKey = env('SAFARICOM_DARAJA_CONSUMER_KEY');
        $consumerSecret = env('SAFARICOM_DARAJA_CONSUMER_SECRET');

        if (!$consumerKey || !$consumerSecret) {
            Log::error('Daraja API: Consumer key or secret is missing.');
            return null;
        }

        $url = env('SAFARICOM_DARAJA_BASE_URL') . '/oauth/v1/generate?grant_type=client_credentials';

        try {
            $response = Http::withBasicAuth($consumerKey, $consumerSecret)
                ->get($url)
                ->json();

            if (isset($response['access_token'])) {
                return $response['access_token'];
            } else {
                Log::error('Daraja API: Failed to get access token.', ['response' => $response]);
                return null;
            }
        } catch (Exception $e) {
            Log::error('Daraja API: Access token request failed.', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Initiate B2C payment to a phone number.
     *
     * @param string $phoneNumber
     * @param float $amount
     * @param string $reference
     * @return array
     */

    public static function initiateB2CPayment($phoneNumber, $amount, $reference)
    {
        $accessToken = self::getAccessToken();

        if (!$accessToken) {
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve access token.',
            ];
        }

        $url = env('SAFARICOM_DARAJA_BASE_URL') . '/mpesa/b2c/v1/paymentrequest';

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json',
        ];

        $timestamp = date('YmdHis');

        // Format B2C URLs to ensure they include a protocol
        $queueTimeOutURL = self::formatUrl(env('SAFARICOM_B2C_TIMEOUT_URL'));
        $resultURL       = self::formatUrl(env('SAFARICOM_B2C_RESULT_URL'));

        $payload = [
            'InitiatorName'      => env('SAFARICOM_INITIATOR_NAME'),
            'SecurityCredential' => env('SecurityCredential'),
            'CommandID'          => 'BusinessPayment',
            'Amount'             => $amount,
            'PartyA'             => env('SAFARICOM_SHORTCODE'),
            'PartyB'             => $phoneNumber,
            'Remarks'            => $reference,
            'QueueTimeOutURL'    => $queueTimeOutURL,
            'ResultURL'          => $resultURL,
            'Occasion'           => $reference,
        ];

        try {
            $response = Http::withHeaders($headers)->post($url, $payload);
            $result = $response->json();

            if ($response->successful() && isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
                return [
                    'status'  => 'success',
                    'message' => 'Payment sent successfully.',
                    'data'    => $result,
                ];
            } else {
                Log::error('B2C Payment Failed', ['response' => $result]);
                return [
                    'status'  => 'error',
                    'message' => $result['errorMessage'] ?? 'Payment failed.',
                    'data'    => $result,
                ];
            }
        } catch (Exception $e) {
            Log::error('B2C Payment Request Failed', ['error' => $e->getMessage()]);
            return [
                'status'  => 'error',
                'message' => 'Payment request failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Initiate STK Push (Lipa na Mpesa Online).
     *
     * @param string $phoneNumber
     * @param float $amount
     * @param string $reference
     * @param string $description
     * @return array
     */

    public static function stkPushRequest($phoneNumber, $amount, $reference, $description)
    {
        $accessToken = self::getAccessToken();

        if (!$accessToken) {
            Log::error('Daraja API: Failed to retrieve access token.');
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve access token.',
            ];
        }

        $url = env('SAFARICOM_DARAJA_BASE_URL') . '/mpesa/stkpush/v1/processrequest';

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json',
        ];

        $timestamp = date('YmdHis');
        $password = base64_encode(env('SAFARICOM_SHORTCODE') . env('SAFARICOM_PASSKEY') . $timestamp);

        // Format the callback URL to ensure proper protocol
        $callbackUrl = self::formatUrl(env('SAFARICOM_STK_CALLBACK_URL'));

        $payload = [
            'BusinessShortCode' => env('SAFARICOM_SHORTCODE'),
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'TransactionType'   => 'CustomerPayBillOnline',
            'Amount'            => $amount,
            'PartyA'            => $phoneNumber,
            'PartyB'            => env('SAFARICOM_SHORTCODE'),
            'PhoneNumber'       => $phoneNumber,
            'CallBackURL'       => $callbackUrl,
            'AccountReference'  => $reference,
            'TransactionDesc'   => $description,
        ];

        try {
            $response = Http::withHeaders($headers)->post($url, $payload);
            $result = $response->json();

            if ($response->successful() && isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
                Log::info('STK Push Sent Successfully', ['response' => $result]);
                return [
                    'status'  => 'success',
                    'message' => 'STK Push sent successfully.',
                    'data'    => $result,
                ];
            } else {
                Log::error('STK Push Failed', ['response' => $result]);
                return [
                    'status'  => 'error',
                    'message' => $result['errorMessage'] ?? 'STK Push failed.',
                    'data'    => $result,
                ];
            }
        } catch (Exception $e) {
            Log::error('STK Push Request Failed', ['error' => $e->getMessage()]);
            return [
                'status'  => 'error',
                'message' => 'STK Push request failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get the paybill balance.
     *
     * @return array
     */

    public static function getPaybillBalance()
    {
        $accessToken = self::getAccessToken();

        if (!$accessToken) {
            return [
                'status'  => 'error',
                'message' => 'Failed to retrieve access token.',
            ];
        }

        $url = env('SAFARICOM_DARAJA_BASE_URL') . '/mpesa/accountbalance/v1/query';

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json',
        ];

        $timestamp = date('YmdHis');
        // Compute the security credential (password) similar to the STK push process
        $password = base64_encode(env('SAFARICOM_SHORTCODE') . env('SAFARICOM_PASSKEY') . $timestamp);

        $queueTimeOutURL = self::formatUrl(env('SAFARICOM_BALANCE_TIMEOUT_URL'));
        $resultURL       = self::formatUrl(env('SAFARICOM_BALANCE_RESULT_URL'));

        $payload = [
            'Initiator'          => env('SAFARICOM_INITIATOR_NAME'),
            'SecurityCredential' => env('SecurityCredential'),
            'CommandID'          => 'AccountBalance',
            'PartyA'             => env('SAFARICOM_SHORTCODE'),
            'IdentifierType'     => '4',
            'Remarks'            => 'Balance Inquiry',
            'QueueTimeOutURL'    => $queueTimeOutURL,
            'ResultURL'          => $resultURL,
        ];

        try {
            $response = Http::withHeaders($headers)->post($url, $payload);
            $result = $response->json();

            if ($response->successful() && isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
                return [
                    'status'  => 'success',
                    'message' => 'Balance inquiry successful.',
                    'data'    => $result,
                ];
            } else {
                Log::error('Account Balance Inquiry Failed', ['response' => $result]);
                return [
                    'status'  => 'error',
                    'message' => $result['errorMessage'] ?? 'Balance inquiry failed.',
                    'data'    => $result,
                ];
            }
        } catch (Exception $e) {
            Log::error('Account Balance Inquiry Request Failed', ['error' => $e->getMessage()]);
            return [
                'status'  => 'error',
                'message' => 'Balance inquiry request failed: ' . $e->getMessage(),
            ];
        }
    }

/**
 * Initiate B2B payment between businesses.
 *
 * @param string $receiverShortCode The receiving business shortcode.
 * @param float $amount The amount to transfer.
 * @param string $remarks Remarks for the transaction.
 * @return array
 */

public static function initiateB2BPayment($receiverShortCode, $amount, $remarks, $accountReference)
{
    $accessToken = self::getAccessToken();

    if (!$accessToken) {
        return [
            'status'  => 'error',
            'message' => 'Failed to retrieve access token.',
        ];
    }

    $url = env('SAFARICOM_DARAJA_BASE_URL') . '/mpesa/b2b/v1/paymentrequest';

    $headers = [
        'Authorization' => 'Bearer ' . $accessToken,
        'Content-Type'  => 'application/json',
    ];

    $payload = [
        'Initiator'              => env('SAFARICOM_INITIATOR_NAME'),
        'SecurityCredential'     => env('SecurityCredential'), // fix env key
        'CommandID'              => 'BusinessPayBill', // Paybill command for utility accounts
        'SenderIdentifierType'   => '4',
        'RecieverIdentifierType' => '4', // keep typo per API spec
        'Amount'                 => $amount,
        'PartyA'                 => env('SAFARICOM_SHORTCODE'),
        'PartyB'                 => $receiverShortCode,   // Paybill shortcode here
        'AccountReference'       => $accountReference,    // e.g. customer number, invoice
        'Remarks'                => $remarks,
        'QueueTimeOutURL'        => self::formatUrl(env('SAFARICOM_B2B_TIMEOUT_URL')),
        'ResultURL'              => self::formatUrl(env('SAFARICOM_B2B_RESULT_URL')),
    ];

    try {
        $response = Http::withHeaders($headers)->post($url, $payload);
        $result = $response->json();

        if ($response->successful() && isset($result['ResponseCode']) && $result['ResponseCode'] === '0') {
            return [
                'status'  => 'success',
                'message' => 'B2B payment initiated successfully.',
                'data'    => $result,
            ];
        } else {
            Log::error('B2B Payment Failed', ['response' => $result]);
            return [
                'status'  => 'error',
                'message' => $result['errorMessage'] ?? 'B2B payment failed.',
                'data'    => $result,
            ];
        }
    } catch (Exception $e) {
        Log::error('B2B Payment Request Failed', ['error' => $e->getMessage()]);
        return [
            'status'  => 'error',
            'message' => 'B2B payment request failed: ' . $e->getMessage(),
        ];
    }
}


public static function initiateB2BTillPayment($tillNumber, $amount, $remarks)
{
    $accessToken = self::getAccessToken();

    if (!$accessToken) {
        return [
            'status'  => 'error',
            'message' => 'Failed to retrieve access token.',
        ];
    }

    $url = env('SAFARICOM_DARAJA_BASE_URL') . '/mpesa/b2b/v1/paymentrequest';

    $headers = [
        'Authorization' => 'Bearer ' . $accessToken,
        'Content-Type'  => 'application/json',
    ];

    $payload = [
        'Initiator'              => env('SAFARICOM_INITIATOR_NAME'),
        'SecurityCredential'     => env('SecurityCredential'),
        'CommandID'              => 'BusinessBuyGoods', // Command for Till number payments
        'SenderIdentifierType'   => '4',
        'RecieverIdentifierType' => '4', // API expects this misspelling
        'Amount'                 => $amount,
        'PartyA'                 => env('SAFARICOM_SHORTCODE'), // Your shortcode
        'PartyB'                 => $tillNumber,               // The Till number to receive payment
        'Remarks'                => $remarks,
        'QueueTimeOutURL'        => self::formatUrl(env('SAFARICOM_B2B_TIMEOUT_URL')),
        'ResultURL'              => self::formatUrl(env('SAFARICOM_B2B_RESULT_URL')),
    ];

    try {
        $response = Http::withHeaders($headers)->post($url, $payload);
        $result = $response->json();

        if ($response->successful() && isset($result['ResponseCode']) && $result['ResponseCode'] === '0') {
            return [
                'status'  => 'success',
                'message' => 'B2B Till payment initiated successfully.',
                'data'    => $result,
            ];
        } else {
            Log::error('B2B Till Payment Failed', ['response' => $result]);
            return [
                'status'  => 'error',
                'message' => $result['errorMessage'] ?? 'B2B Till payment failed.',
                'data'    => $result,
            ];
        }
    } catch (Exception $e) {
        Log::error('B2B Till Payment Request Failed', ['error' => $e->getMessage()]);
        return [
            'status'  => 'error',
            'message' => 'B2B Till payment request failed: ' . $e->getMessage(),
        ];
    }
}


public static function validateStkTransaction(string $checkoutRequestID): array
{
    $accessToken = self::getAccessToken();

    if (!$accessToken) {
        return [
            'status'  => 'error',
            'message' => 'Failed to retrieve access token.',
        ];
    }

    $timestamp = now()->format('YmdHis');
    $shortCode = env('SAFARICOM_SHORTCODE');
    $passkey   = env('SAFARICOM_PASSKEY');
    $password  = base64_encode($shortCode . $passkey . $timestamp);

    $payload = [
        'BusinessShortCode' => $shortCode,
        'Password'          => $password,
        'Timestamp'         => $timestamp,
        'CheckoutRequestID' => $checkoutRequestID,
    ];

    $url = rtrim(env('SAFARICOM_DARAJA_BASE_URL'), '/') . '/mpesa/stkpushquery/v1/query';

    try {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json',
        ])->post($url, $payload);

        $result = $response->json();

        Log::info('STK Validation Response', ['response' => $result]);

        // Check for typical success or error keys in the response
        if (isset($result['ResultDesc'])) {
            return [
                'status'      => 'success',
                'message'     => $result['ResultDesc'],
                'resultCode'  => $result['ResultCode'] ?? null,
                'checkoutId'  => $checkoutRequestID,
                'data'        => $result,
            ];
        }

        if (isset($result['errorMessage'])) {
            return [
                'status'  => 'error',
                'message' => $result['errorMessage'],
                'data'    => $result,
            ];
        }

        // Unexpected response structure
        return [
            'status'  => 'error',
            'message' => 'Validation response unexpected. Check Safaricom API logs.',
            'data'    => $result,
        ];

    } catch (\Illuminate\Http\Client\RequestException $exception) {
        $errorResponse = $exception->response ? $exception->response->json() : [];
        Log::error('STK Validation Request Exception', [
            'error'    => $exception->getMessage(),
            'response' => $errorResponse,
        ]);

        return [
            'status'  => 'error',
            'message' => $errorResponse['errorMessage'] ?? 'STK validation request failed.',
            'data'    => $errorResponse,
        ];
    } catch (\Exception $e) {
        Log::error('STK Validation Uncaught Exception', ['error' => $e->getMessage()]);

        return [
            'status'  => 'error',
            'message' => 'STK validation request failed: ' . $e->getMessage(),
        ];
    }
}




}
