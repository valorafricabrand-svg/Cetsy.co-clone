<?php

// app/Http/Controllers/Seller/PayoutRequestController.php
namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\PayoutRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentMethod;
use App\Models\Shop;
use App\Models\Activity;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PayoutRequestController extends Controller
{
    public function index()
    {
        $balance = Wallet::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->selectRaw('SUM(credit) - SUM(debit) AS bal')
            ->value('bal') ?? 0;

        $requests = PayoutRequest::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('seller.payouts.index', compact('balance', 'requests'));
    }

    public function store(Request $request)
    {
        // Balance and fee rate
        $balance = Wallet::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->selectRaw('SUM(credit) - SUM(debit) AS balance')
            ->value('balance') ?? 0;

        // Pull from settings table with sensible defaults
        // fee_rate is stored as percent in settings (e.g., 1.5). Normalize to decimal.
        $rawFee    = (float) (function_exists('setting') ? setting('fee_rate', 1.5) : 1.5);
        $feeRate   = $rawFee > 1 ? $rawFee / 100 : $rawFee;
        $minAmount = (float) (function_exists('setting') ? setting('min_amount', 1) : 1);

        $request->validate([
            'amount' => 'required|numeric|min:'.$minAmount,
            'method' => 'required|exists:payment_methods,id',
        ]);

        $amount = (float) $request->amount;
        $fee    = round($amount * $feeRate, 2);
        if (($amount + $fee) > $balance + 0.00001) {
            return back()->withErrors(['amount' => 'Amount plus fee exceeds your available balance.'])->withInput();
        }

        $shop = Shop::where('user_id', Auth::id())->first();
        $paymentMethod = PaymentMethod::where('shop_id', optional($shop)->id)
            ->where('id', $request->method)
            ->first();
        abort_if(!$paymentMethod, 403, 'Invalid payout method');

        // Create payout request in OTP pending state; do NOT debit wallet yet
        $otp = random_int(100000, 999999);
        $otpHash = Hash::make((string) $otp);
        $otpExpires = now()->addMinutes(10)->toISOString();

        $payout = PayoutRequest::create([
            'wallet_id'         => null,
            'user_id'           => Auth::id(),
            'amount'            => $amount,
            'payment_method_id' => $request->method,
            'status'            => 'otp_pending',
            'meta'              => [
                'method'          => $request->method,
                'fee'             => $fee,
                'fee_rate'        => $feeRate,
                'otp_hash'        => $otpHash,
                'otp_expires_at'  => $otpExpires,
                'otp_attempts'    => 0,
                'otp_resend_count'=> 0,
                'otp_last_sent_at'=> now()->toISOString(),
            ],
        ]);

        // Send OTP email (best-effort)
        try {
            $user = Auth::user();
            if ($user && $user->email) {
                \Mail::to($user->email)->send(new \App\Mail\PayoutOtpMail($payout, $user, $otp));
            }
        } catch (\Throwable $e) {}

        return redirect()->route('seller.payouts.verify', $payout)
            ->with('success', 'We sent a verification code to your email. Enter it to confirm the payout request.');
    }

    /**
     * Show OTP verification form.
     */
    public function verifyForm(PayoutRequest $payout)
    {
        abort_if($payout->user_id !== Auth::id(), 403);
        abort_if($payout->status !== 'otp_pending', 409, 'This payout is not awaiting verification.');
        $meta = $payout->meta ?? [];
        $lastSentIso = data_get($meta, 'otp_last_sent_at');
        $resendCount = (int) data_get($meta, 'otp_resend_count', 0);
        $cooldownSec = 120; // 2 minutes
        $canResendAt = null;
        if ($lastSentIso) {
            try { $canResendAt = Carbon::parse($lastSentIso)->addSeconds($cooldownSec); } catch (\Throwable $e) {}
        }
        return view('seller.payouts.verify', compact('payout', 'resendCount', 'canResendAt', 'cooldownSec'));
    }

    /**
     * Handle OTP verification and finalize payout request (debit wallet and set status pending).
     */
    public function verifyOtp(Request $request, PayoutRequest $payout)
    {
        abort_if($payout->user_id !== Auth::id(), 403);
        abort_if($payout->status !== 'otp_pending', 409, 'This payout is not awaiting verification.');

        $data = $request->validate([
            'code' => 'required|string|min:4|max:8',
        ]);

        $meta = $payout->meta ?? [];
        $expires = data_get($meta, 'otp_expires_at');
        $hash    = data_get($meta, 'otp_hash');
        $attempts= (int) data_get($meta, 'otp_attempts', 0);
        if ($attempts >= 5) {
            return back()->withErrors(['code' => 'Too many attempts. Please request a new payout.']);
        }
        if ($expires && now()->greaterThan(\Carbon\Carbon::parse($expires))) {
            return back()->withErrors(['code' => 'This code has expired. Please request a new payout.']);
        }
        if (!$hash || !Hash::check($data['code'], $hash)) {
            $meta['otp_attempts'] = $attempts + 1;
            $payout->update(['meta' => $meta]);
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        // Passed verification; proceed with wallet debits within a transaction
        DB::transaction(function () use ($payout, &$meta) {
            $userId = $payout->user_id;
            $amount = (float) $payout->amount;
            $fee    = (float) ($meta['fee'] ?? 0);
            $feeRate= (float) ($meta['fee_rate'] ?? 0);

            $debitRow = Wallet::create([
                'user_id'     => $userId,
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
                    'user_id'     => $userId,
                    'credit'      => 0,
                    'debit'       => $fee,
                    'balance'     => 0,
                    'type'        => 'withdrawal_fee',
                    'reference'   => Str::uuid(),
                    'description' => 'Withdrawal fee',
                    'meta'        => ['rate' => $feeRate],
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

            Activity::create([
                'user_id'      => $userId,
                'is_read'      => false,
                'description'  => 'You submitted a new payout request of $' . number_format($amount, 2),
                'type'         => \App\Models\Activity::TYPE_PAYOUT,
                'related_id'   => $debitRow->id,
                'related_type' => 'wallet',
            ]);
        });

        return redirect()->route('seller.payouts.index')->with('success', 'Verification successful. Your payout request was submitted.');
    }

    /**
     * Resend OTP with rate limiting.
     */
    public function resendOtp(PayoutRequest $payout)
    {
        abort_if($payout->user_id !== Auth::id(), 403);
        abort_if($payout->status !== 'otp_pending', 409, 'This payout is not awaiting verification.');

        $meta = $payout->meta ?? [];
        $resendCount = (int) data_get($meta, 'otp_resend_count', 0);
        $lastSentIso = data_get($meta, 'otp_last_sent_at');
        $cooldownSec = 120; // 2 minutes
        $maxResends  = 3;

        if ($resendCount >= $maxResends) {
            return back()->withErrors(['code' => 'You have reached the maximum number of resend attempts. Please create a new payout request.']);
        }
        if ($lastSentIso) {
            try {
                $next = Carbon::parse($lastSentIso)->addSeconds($cooldownSec);
                if (now()->lt($next)) {
                    $wait = $next->diffInSeconds(now());
                    return back()->withErrors(['code' => 'Please wait '.$wait.' seconds before requesting another code.']);
                }
            } catch (\Throwable $e) {}
        }

        $otp = (string) random_int(100000, 999999);
        $meta['otp_hash'] = Hash::make($otp);
        $meta['otp_expires_at'] = now()->addMinutes(10)->toISOString();
        $meta['otp_attempts'] = 0; // reset attempts on resend
        $meta['otp_resend_count'] = $resendCount + 1;
        $meta['otp_last_sent_at'] = now()->toISOString();

        $payout->update(['meta' => $meta]);

        try {
            $user = Auth::user();
            if ($user && $user->email) {
                \Mail::to($user->email)->send(new \App\Mail\PayoutOtpMail($payout, $user, $otp));
            }
        } catch (\Throwable $e) {}

        return back()->with('success', 'A new verification code has been sent.');
    }

    /**
     * Cancel an OTP-pending payout request.
     */
    public function cancel(PayoutRequest $payout)
    {
        abort_if($payout->user_id !== Auth::id(), 403);
        abort_if($payout->status !== 'otp_pending', 409, 'This payout can no longer be cancelled.');

        $meta = $payout->meta ?? [];
        $meta['cancelled_at'] = now()->toISOString();

        $payout->update([
            'status' => 'cancelled',
            'meta'   => $meta,
        ]);

        return redirect()->route('seller.payouts.index')->with('success', 'Payout request cancelled.');
    }
}
