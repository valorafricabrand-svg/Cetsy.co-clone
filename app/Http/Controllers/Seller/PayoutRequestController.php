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
use Illuminate\Support\Facades\Log;

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

        // Highlight any payout awaiting OTP verification
        $otpPending = PayoutRequest::where('user_id', Auth::id())
            ->where('status', 'otp_pending')
            ->latest('id')
            ->first();

        return view('seller.payouts.index', compact('balance', 'requests', 'otpPending'));
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

        try {
            $verifyUrl = route('seller.payouts.otp.verify', $payout);
        } catch (\Throwable $e) {
            $verifyUrl = url('/seller/payouts/'.$payout->id.'/verify');
        }
        return redirect()->to($verifyUrl)
            ->with('success', 'We sent a verification code to your email. Enter it to confirm the payout request.');
    }

    /**
     * Show OTP verification form.
     */
    public function verifyForm($payout)
    {
        $payout = PayoutRequest::find($payout);
        if (! $payout) {
            Log::warning('payout.verifyForm not_found', [
                'payout_param' => $payout,
                'auth_id'      => Auth::id(),
            ]);
            return redirect()->route('seller.payouts.index')->withErrors('Payout request not found or already finalized.');
        }
        if ($payout->user_id !== Auth::id()) {
            Log::warning('payout.verifyForm unauthorized', [
                'payout_id' => $payout->id,
                'owner_id'  => $payout->user_id,
                'auth_id'   => Auth::id(),
                'ip'        => request()->ip(),
            ]);
            return redirect()->route('seller.payouts.index')->withErrors('You are not allowed to access that payout request.');
        }
        if ($payout->status !== 'otp_pending') {
            Log::warning('payout.verifyForm wrong_status', [
                'payout_id' => $payout->id,
                'status'    => $payout->status,
                'auth_id'   => Auth::id(),
            ]);
            return redirect()->route('seller.payouts.index')
                ->withErrors('This payout is not awaiting verification.');
        }
        Log::info('payout.verifyForm serve', [
            'payout_id' => $payout->id,
            'auth_id'   => Auth::id(),
            'ip'        => request()->ip(),
            'ua'        => request()->userAgent(),
        ]);
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
    public function verifyOtp(Request $request, $payout)
    {
        $payout = PayoutRequest::find($payout);
        if (! $payout) {
            Log::warning('payout.verifyOtp not_found', [
                'payout_param' => $payout,
                'auth_id'      => Auth::id(),
            ]);
            return redirect()->route('seller.payouts.index')->withErrors('Payout request not found or already finalized.');
        }
        if ($payout->user_id !== Auth::id()) {
            Log::warning('payout.verifyOtp unauthorized', [
                'payout_id' => $payout->id,
                'owner_id'  => $payout->user_id,
                'auth_id'   => Auth::id(),
                'ip'        => $request->ip(),
            ]);
            return redirect()->route('seller.payouts.index')->withErrors('You are not allowed to verify this payout.');
        }
        if ($payout->status !== 'otp_pending') {
            Log::warning('payout.verifyOtp wrong_status', [
                'payout_id' => $payout->id,
                'status'    => $payout->status,
                'auth_id'   => Auth::id(),
            ]);
            return redirect()->route('seller.payouts.index')
                ->withErrors('This payout is not awaiting verification.');
        }
        Log::info('payout.verifyOtp submit', [
            'payout_id' => $payout->id,
            'auth_id'   => Auth::id(),
            'ip'        => $request->ip(),
            'ua'        => $request->userAgent(),
        ]);

        $data = $request->validate([
            'code' => 'required|string|min:4|max:8',
        ]);

        $meta = $payout->meta ?? [];
        $expires = data_get($meta, 'otp_expires_at');
        $hash    = data_get($meta, 'otp_hash');
        $attempts= (int) data_get($meta, 'otp_attempts', 0);
        if ($attempts >= 5) {
            Log::warning('payout.verifyOtp attempts_limit', [
                'payout_id' => $payout->id,
                'auth_id'   => Auth::id(),
                'attempts'  => $attempts,
            ]);
            return back()->withErrors(['code' => 'Too many attempts. Please request a new payout.']);
        }
        if ($expires && now()->greaterThan(\Carbon\Carbon::parse($expires))) {
            Log::warning('payout.verifyOtp expired', [
                'payout_id' => $payout->id,
                'auth_id'   => Auth::id(),
                'expires'   => $expires,
            ]);
            return back()->withErrors(['code' => 'This code has expired. Please request a new payout.']);
        }
        if (!$hash || !Hash::check($data['code'], $hash)) {
            $meta['otp_attempts'] = $attempts + 1;
            $payout->update(['meta' => $meta]);
            Log::warning('payout.verifyOtp invalid_code', [
                'payout_id' => $payout->id,
                'auth_id'   => Auth::id(),
                'attempts'  => $meta['otp_attempts'],
            ]);
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        // Before debiting, re-check available balance and ensure payout method still exists
        $currentBalance = Wallet::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->selectRaw('SUM(credit - debit) as balance')
            ->value('balance') ?? 0;

        $amount = (float) $payout->amount;
        $fee    = (float) ($meta['fee'] ?? 0);
        if (($amount + $fee) > ($currentBalance + 0.00001)) {
            Log::warning('payout.verifyOtp insufficient_balance', [
                'payout_id' => $payout->id,
                'auth_id'   => Auth::id(),
                'amount'    => $amount,
                'fee'       => $fee,
                'balance'   => $currentBalance,
            ]);
            return back()->withErrors(['code' => 'Insufficient balance to finalize payout. Reduce amount or cancel the request.']);
        }

        $methodId = (int) ($meta['method'] ?? 0);
        $shop = Shop::where('user_id', Auth::id())->first();
        $paymentMethod = $shop ? PaymentMethod::where('shop_id', $shop->id)->where('id', $methodId)->first() : null;
        if (!$paymentMethod) {
            Log::warning('payout.verifyOtp missing_method', [
                'payout_id' => $payout->id,
                'auth_id'   => Auth::id(),
                'method_id' => $methodId,
            ]);
            return back()->withErrors(['code' => 'Selected payout method no longer exists. Please add a payout method and create a new request.']);
        }

        // Passed verification; proceed with wallet debits within a transaction
        DB::transaction(function () use ($payout, &$meta, $amount, $fee) {
            $userId = $payout->user_id;
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
            Log::info('payout.verifyOtp verified', [
                'payout_id' => $payout->id,
                'auth_id'   => $userId,
                'wallet_id' => $debitRow->id,
                'amount'    => $amount,
                'fee'       => $fee,
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
    public function resendOtp($payout)
    {
        $payout = PayoutRequest::find($payout);
        if (! $payout) {
            Log::warning('payout.resendOtp not_found', [
                'payout_param' => $payout,
                'auth_id'      => Auth::id(),
            ]);
            return back()->withErrors(['code' => 'Payout request not found or already finalized.']);
        }
        if ($payout->user_id !== Auth::id()) {
            Log::warning('payout.resendOtp unauthorized', [
                'payout_id' => $payout->id,
                'owner_id'  => $payout->user_id,
                'auth_id'   => Auth::id(),
                'ip'        => request()->ip(),
            ]);
            return back()->withErrors(['code' => 'You are not allowed to resend this code.']);
        }
        if ($payout->status !== 'otp_pending') {
            Log::warning('payout.resendOtp wrong_status', [
                'payout_id' => $payout->id,
                'status'    => $payout->status,
                'auth_id'   => Auth::id(),
            ]);
            return back()->withErrors(['code' => 'This payout is not awaiting verification.']);
        }

        $meta = $payout->meta ?? [];
        $resendCount = (int) data_get($meta, 'otp_resend_count', 0);
        $lastSentIso = data_get($meta, 'otp_last_sent_at');
        $cooldownSec = 120; // 2 minutes
        $maxResends  = 3;

        if ($resendCount >= $maxResends) {
            Log::warning('payout.resendOtp max_resends', [
                'payout_id' => $payout->id,
                'auth_id'   => Auth::id(),
                'resends'   => $resendCount,
            ]);
            return back()->withErrors(['code' => 'You have reached the maximum number of resend attempts. Please create a new payout request.']);
        }
        if ($lastSentIso) {
            try {
                $next = Carbon::parse($lastSentIso)->addSeconds($cooldownSec);
                if (now()->lt($next)) {
                    $wait = $next->diffInSeconds(now());
                    Log::info('payout.resendOtp throttled', [
                        'payout_id' => $payout->id,
                        'auth_id'   => Auth::id(),
                        'wait_sec'  => $wait,
                    ]);
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
        } catch (\Throwable $e) {
            Log::error('payout.resendOtp mail_error', [
                'payout_id' => $payout->id,
                'auth_id'   => Auth::id(),
                'error'     => $e->getMessage(),
            ]);
        }

        Log::info('payout.resendOtp sent', [
            'payout_id' => $payout->id,
            'auth_id'   => Auth::id(),
            'resends'   => $meta['otp_resend_count'] ?? null,
        ]);
        return back()->with('success', 'A new verification code has been sent.');
    }

    /**
     * Cancel an OTP-pending payout request.
     */
    public function cancel($payout)
    {
        $payout = PayoutRequest::find($payout);
        if (! $payout) {
            Log::warning('payout.cancel not_found', [
                'payout_param' => $payout,
                'auth_id'      => Auth::id(),
            ]);
            return redirect()->route('seller.payouts.index')->withErrors('Payout request not found or already finalized.');
        }
        if ($payout->user_id !== Auth::id()) {
            Log::warning('payout.cancel unauthorized', [
                'payout_id' => $payout->id,
                'owner_id'  => $payout->user_id,
                'auth_id'   => Auth::id(),
            ]);
            return redirect()->route('seller.payouts.index')->withErrors('You are not allowed to cancel this payout.');
        }
        if ($payout->status !== 'otp_pending') {
            Log::warning('payout.cancel wrong_status', [
                'payout_id' => $payout->id,
                'status'    => $payout->status,
                'auth_id'   => Auth::id(),
            ]);
            return redirect()->route('seller.payouts.index')->withErrors('This payout can no longer be cancelled.');
        }

        $meta = $payout->meta ?? [];
        $meta['cancelled_at'] = now()->toISOString();

        $payout->update([
            'status' => 'cancelled',
            'meta'   => $meta,
        ]);

        Log::info('payout.cancel success', [
            'payout_id' => $payout->id,
            'auth_id'   => Auth::id(),
        ]);
        return redirect()->route('seller.payouts.index')->with('success', 'Payout request cancelled.');
    }
}
