<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\PayoutRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\PaymentMethod;
use App\Models\Shop;

class WalletController extends Controller
{
    /**
     * Return wallet summary for the authenticated user.
     */
    public function summary(Request $request)
    {
        $user = $request->user();
        if (! $user) return response()->json(['message' => 'Unauthorized'], 401);

        $balance = (float) wallet('completed', $user->id);

        $onHold = (float) wallet('on_hold', $user->id);

        $recent = Wallet::where('user_id', $user->id)
            ->latest('id')
            ->limit(5)
            ->get(['id','credit','debit','balance','description','method','status','created_at']);

        // payout settings
        $rawFee    = (float) (function_exists('setting') ? setting('fee_rate', 1.5) : 1.5);
        $feeRate   = $rawFee > 1 ? $rawFee / 100 : $rawFee;
        $minAmount = (float) (function_exists('setting') ? setting('min_amount', 1) : 1);
        $maxPayout = $feeRate > 0 ? max(0, floor(($balance / (1 + $feeRate)) * 100) / 100) : $balance;

        return response()->json([
            'balance' => $balance,
            'on_hold' => $onHold,
            'recent'  => $recent,
            'payout'  => [
                'fee_rate' => $feeRate,
                'min_amount' => $minAmount,
                'max_amount' => $maxPayout,
            ],
        ]);
    }

    /**
     * Create a payout request for the authenticated user.
     */
    public function requestPayout(Request $request)
    {
        $user = $request->user();
        if (! $user) return response()->json(['message' => 'Unauthorized'], 401);

        $data = $request->validate([
            'amount' => ['required','numeric','min:1'],
            'payment_method_id' => ['nullable','integer'],
        ]);

        $balance = (float) wallet('completed', $user->id);

        $rawFee    = (float) (function_exists('setting') ? setting('fee_rate', 1.5) : 1.5);
        $feeRate   = $rawFee > 1 ? $rawFee / 100 : $rawFee;
        $minAmount = (float) (function_exists('setting') ? setting('min_amount', 1) : 1);
        $maxPayout = $feeRate > 0 ? max(0, floor(($balance / (1 + $feeRate)) * 100) / 100) : $balance;

        if ($data['amount'] < $minAmount) {
            return response()->json(['message' => 'Amount below minimum.'], 422);
        }
        if ($data['amount'] > $maxPayout) {
            return response()->json(['message' => 'Amount exceeds available balance.'], 422);
        }
        // Validate provided payout method belongs to the seller (if any)
        $paymentMethodId = $data['payment_method_id'] ?? null;
        if ($paymentMethodId) {
            $shop = Shop::where('user_id', $user->id)->first();
            $methodExists = $shop ? PaymentMethod::where('shop_id', $shop->id)->where('id', $paymentMethodId)->exists() : false;
            if (! $methodExists) {
                return response()->json(['message' => 'Invalid payout method.'], 422);
            }
        }

        // Create payout in OTP-pending state (do not debit wallet yet)
        $otp = (string) random_int(100000, 999999);
        $otpHash = Hash::make($otp);
        $otpExpires = now()->addMinutes(10)->toISOString();

        $payout = PayoutRequest::create([
            'user_id'           => $user->id,
            'amount'            => (float) $data['amount'],
            'status'            => 'otp_pending',
            'payment_method_id' => $paymentMethodId,
            'meta'              => [
                'fee_rate'         => $feeRate,
                'fee'              => round(((float)$data['amount']) * $feeRate, 2),
                'otp_hash'         => $otpHash,
                'otp_expires_at'   => $otpExpires,
                'otp_attempts'     => 0,
                'otp_resend_count' => 0,
                'otp_last_sent_at' => now()->toISOString(),
            ],
        ]);

        // Best-effort email with code
        try {
            if ($user->email) {
                \Mail::to($user->email)->send(new \App\Mail\PayoutOtpMail($payout, $user, $otp));
            }
        } catch (\Throwable $e) {
            Log::error('api.payout.otp_mail_error', ['payout_id' => $payout->id, 'user_id' => $user->id, 'error' => $e->getMessage()]);
        }

        return response()->json([
            'message'      => 'Verification code sent. Enter the code to confirm payout.',
            'requires_otp' => true,
            'payout_id'    => $payout->id,
            'expires_in'   => 600,
        ], 201);
    }

    /**
     * Paginated list of wallet transactions for the authenticated user.
     */
    public function transactions(Request $request)
    {
        $user = $request->user();
        if (! $user) return response()->json(['message' => 'Unauthorized'], 401);

        $query = Wallet::where('user_id', $user->id)->latest('id');
        if ($request->type === 'credit') {
            $query->where('credit', '>', 0);
        } elseif ($request->type === 'debit') {
            $query->where('debit', '>', 0);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->get('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->get('to'));
        }

        return $query->paginate(15, ['id','credit','debit','balance','description','method','status','created_at']);
    }

    /**
     * Return PayPal client id for client-side checkout.
     */
    public function paypalConfig(Request $request)
    {
        $user = $request->user();
        if (! $user) return response()->json(['message' => 'Unauthorized'], 401);

        if (function_exists('payment_gateway_enabled') && !payment_gateway_enabled('paypal')) {
            return response()->json(['enabled' => false, 'client_id' => '']);
        }

        // Prefer .env / config; fall back to settings only if not set
        $clientId = config('services.paypal.client_id') ?: (function_exists('setting') ? (setting('paypal_client_id') ?? '') : '');
        return response()->json(['enabled' => true, 'client_id' => $clientId]);
    }

    /**
     * Verify payout OTP and finalize payout request (debit wallet).
     */
    public function verifyPayoutOtp(Request $request, $payout)
    {
        $user = $request->user();
        if (! $user) return response()->json(['message' => 'Unauthorized'], 401);

        $payout = PayoutRequest::find($payout);
        if (! $payout || $payout->user_id !== $user->id) {
            return response()->json(['message' => 'Payout request not found'], 404);
        }
        if ($payout->status !== 'otp_pending') {
            return response()->json(['message' => 'This payout is not awaiting verification.'], 422);
        }

        $data = $request->validate(['code' => 'required|string|min:4|max:8']);

        $meta = $payout->meta ?? [];
        $expires = data_get($meta, 'otp_expires_at');
        $hash    = data_get($meta, 'otp_hash');
        $attempts= (int) data_get($meta, 'otp_attempts', 0);
        if ($attempts >= 5) {
            return response()->json(['message' => 'Too many attempts. Request a new payout.'], 429);
        }
        if ($expires && now()->greaterThan(Carbon::parse($expires))) {
            return response()->json(['message' => 'Code expired. Request a new payout.'], 422);
        }
        if (!$hash || !Hash::check($data['code'], $hash)) {
            $meta['otp_attempts'] = $attempts + 1;
            $payout->update(['meta' => $meta]);
            return response()->json(['message' => 'Invalid code. Try again.'], 422);
        }

        $currentBalance = (float) (Wallet::where('user_id', $user->id)
            ->where('status', 'completed')
            ->selectRaw('COALESCE(SUM(credit - debit),0) as balance')
            ->value('balance') ?? 0);

        $amount = (float) $payout->amount;
        $fee    = (float) ($meta['fee'] ?? 0);
        if (($amount + $fee) > ($currentBalance + 0.00001)) {
            return response()->json(['message' => 'Insufficient balance to finalize payout.'], 422);
        }

        DB::transaction(function () use ($payout, &$meta, $amount, $fee, $user) {
            $debitRow = Wallet::create([
                'user_id'     => $user->id,
                'credit'      => 0,
                'debit'       => $amount,
                'balance'     => 0,
                'type'        => 'payout_request',
                'reference'   => Str::uuid(),
                'description' => 'Payout request (net) debit',
            ]);

            $feeRow = null;
            if ($fee > 0) {
                $feeRow = Wallet::create([
                    'user_id'     => $user->id,
                    'credit'      => 0,
                    'debit'       => $fee,
                    'balance'     => 0,
                    'type'        => 'withdrawal_fee',
                    'reference'   => Str::uuid(),
                    'description' => 'Withdrawal fee',
                    'meta'        => ['rate' => (float) ($meta['fee_rate'] ?? 0)],
                ]);
            }

            $meta['fee_wallet_id'] = $feeRow?->id;
            unset($meta['otp_hash'], $meta['otp_expires_at'], $meta['otp_attempts']);
            $meta['otp_verified_at'] = now()->toISOString();

            $payout->update([
                'wallet_id' => $debitRow->id,
                'status'    => 'pending',
                'meta'      => $meta,
            ]);
        });

        return response()->json(['message' => 'Verification successful. Payout submitted.', 'status' => 'pending']);
    }

    /** Resend payout OTP (rate limited). */
    public function resendPayoutOtp(Request $request, $payout)
    {
        $user = $request->user();
        if (! $user) return response()->json(['message' => 'Unauthorized'], 401);
        $payout = PayoutRequest::find($payout);
        if (! $payout || $payout->user_id !== $user->id) {
            return response()->json(['message' => 'Payout request not found'], 404);
        }
        if ($payout->status !== 'otp_pending') {
            return response()->json(['message' => 'This payout is not awaiting verification.'], 422);
        }

        $meta = $payout->meta ?? [];
        $resendCount = (int) data_get($meta, 'otp_resend_count', 0);
        $lastSentIso = data_get($meta, 'otp_last_sent_at');
        $cooldownSec = 120; // 2 minutes
        $maxResends  = 3;

        if ($resendCount >= $maxResends) {
            return response()->json(['message' => 'Maximum resend attempts reached.'], 429);
        }
        if ($lastSentIso) {
            try {
                $next = Carbon::parse($lastSentIso)->addSeconds($cooldownSec);
                if (now()->lt($next)) {
                    $wait = $next->diffInSeconds(now());
                    return response()->json(['message' => 'Please wait '.$wait.' seconds before requesting another code.', 'wait' => $wait], 429);
                }
            } catch (\Throwable $e) {}
        }

        $otp = (string) random_int(100000, 999999);
        $meta['otp_hash'] = Hash::make($otp);
        $meta['otp_expires_at'] = now()->addMinutes(10)->toISOString();
        $meta['otp_attempts'] = 0;
        $meta['otp_resend_count'] = $resendCount + 1;
        $meta['otp_last_sent_at'] = now()->toISOString();
        $payout->update(['meta' => $meta]);

        try {
            if ($user->email) {
                \Mail::to($user->email)->send(new \App\Mail\PayoutOtpMail($payout, $user, $otp));
            }
        } catch (\Throwable $e) {
            Log::error('api.payout.resend_mail_error', ['payout_id' => $payout->id, 'user_id' => $user->id, 'error' => $e->getMessage()]);
        }

        return response()->json(['message' => 'A new verification code has been sent.']);
    }

    /** Cancel an OTP-pending payout request. */
    public function cancelPayout(Request $request, $payout)
    {
        $user = $request->user();
        if (! $user) return response()->json(['message' => 'Unauthorized'], 401);
        $payout = PayoutRequest::find($payout);
        if (! $payout || $payout->user_id !== $user->id) {
            return response()->json(['message' => 'Payout request not found'], 404);
        }
        if ($payout->status !== 'otp_pending') {
            return response()->json(['message' => 'This payout can no longer be cancelled.'], 422);
        }
        $meta = $payout->meta ?? [];
        $meta['cancelled_at'] = now()->toISOString();
        $payout->update(['status' => 'cancelled', 'meta' => $meta]);
        return response()->json(['message' => 'Payout request cancelled.']);
    }
}
