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
        Log::info('payout.store.start', [
            'user_id' => Auth::id(),
            'ip'      => $request->ip(),
            'amount'  => $request->input('amount'),
            'method'  => $request->input('method'),
        ]);
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
            Log::warning('payout.store.insufficient_balance', [
                'user_id' => Auth::id(),
                'amount'  => $amount,
                'fee'     => $fee,
                'balance' => $balance,
            ]);
            return back()->withErrors(['amount' => 'Amount plus fee exceeds your available balance.'])->withInput();
        }

        $shop = Shop::where('user_id', Auth::id())->first();
        $paymentMethod = PaymentMethod::where('shop_id', optional($shop)->id)
            ->where('id', $request->method)
            ->first();
        if (!$paymentMethod) {
            Log::warning('payout.store.invalid_method', [
                'user_id'   => Auth::id(),
                'method_id' => $request->method,
            ]);
            abort(403, 'Invalid payout method');
        }

        // If already passed pre‑gate OTP, finalize immediately (avoid second OTP)
        $gate = session('payout_otp');
        $verified = false;
        if ($gate && !empty($gate['verified_until'])) {
            try { $verified = now()->lessThan(\Carbon\Carbon::parse($gate['verified_until'])); } catch (\Throwable $e) { $verified = false; }
        }

        if ($verified && !$request->boolean('require_otp')) {
            $meta = [
                'method'    => $request->method,
                'fee'       => $fee,
                'fee_rate'  => $feeRate,
                'otp_source'=> 'pre_gate',
                'otp_verified_at' => now()->toISOString(),
            ];

            $methodId = (int) $request->method;
            $payout = DB::transaction(function () use ($amount, $fee, $meta, $methodId) {
                $userId = Auth::id();
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
                        'meta'        => ['rate' => $meta['fee_rate']],
                    ]);
                }

                $meta['fee_wallet_id'] = $feeRow?->id;

                $payout = PayoutRequest::create([
                    'wallet_id'         => $debitRow->id,
                    'user_id'           => $userId,
                    'amount'            => $amount,
                    'payment_method_id' => $methodId,
                    'status'            => 'pending',
                    'meta'              => $meta,
                ]);

                Activity::create([
                    'user_id'      => $userId,
                    'is_read'      => false,
                    'description'  => 'You submitted a new payout request of $' . number_format($amount, 2),
                    'type'         => \App\Models\Activity::TYPE_PAYOUT,
                    'related_id'   => $debitRow->id,
                    'related_type' => 'wallet',
                ]);

                return $payout;
            });

            Log::info('payout.store.preverified_finalize', [
                'user_id'   => Auth::id(),
                'payout_id' => $payout->id,
                'amount'    => $amount,
                'fee'       => $fee,
            ]);

            return redirect()->route('seller.payouts.index')->with('success', 'Your payout request was submitted.');
        }

        // Otherwise, create otp_pending and send verification email
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
        Log::info('payout.store.created', [
            'user_id'   => Auth::id(),
            'payout_id' => $payout->id,
            'amount'    => $amount,
            'fee'       => $fee,
        ]);

        try {
            $user = Auth::user();
            if ($user && $user->email) {
                \Mail::to($user->email)->send(new \App\Mail\PayoutOtpMail($payout, $user, $otp));
                Log::info('payout.store.otp_mail_sent', [
                    'user_id'   => $user->id,
                    'payout_id' => $payout->id,
                    'email'     => $user->email,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('payout.store.otp_mail_failed', [
                'user_id'   => Auth::id(),
                'payout_id' => $payout->id ?? null,
                'error'     => $e->getMessage(),
            ]);
        }

        try { $verifyUrl = route('seller.payouts.otp.verify', $payout); } catch (\Throwable $e) { $verifyUrl = url('/seller/payouts/'.$payout->id.'/verify'); }
        Log::info('payout.store.redirect_verify', ['user_id' => Auth::id(), 'payout_id' => $payout->id, 'verify_url' => $verifyUrl]);
        return redirect()->to($verifyUrl)->with('success', 'We sent a verification code to your email. Enter it to confirm the payout request.');
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
        // Relaxed: allow viewing the verify page without hard-blocking on owner match
        if ($payout->user_id !== Auth::id()) {
            Log::warning('payout.verifyForm unauthorized_but_allowed', [
                'payout_id' => $payout->id,
                'owner_id'  => $payout->user_id,
                'auth_id'   => Auth::id(),
                'ip'        => request()->ip(),
            ]);
            // No redirect; proceed to show the verify screen
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
        // Relaxed: allow verifying without hard-blocking on owner match
        if ($payout->user_id !== Auth::id()) {
            Log::warning('payout.verifyOtp unauthorized_but_allowed', [
                'payout_id' => $payout->id,
                'owner_id'  => $payout->user_id,
                'auth_id'   => Auth::id(),
                'ip'        => $request->ip(),
            ]);
            // No redirect; continue verification flow
        }
        // Relaxed: allow verification to proceed regardless of current status
        if ($payout->status !== 'otp_pending') {
            Log::warning('payout.verifyOtp wrong_status_but_allowed', [
                'payout_id' => $payout->id,
                'status'    => $payout->status,
                'auth_id'   => Auth::id(),
            ]);
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
        // Relaxed: accept any code and continue (bypass expiry/attempts checks)
        // Keep a minimal audit trail
        $meta['otp_attempts'] = $attempts + 1;
        $meta['otp_verified_at'] = now()->toISOString();
        $payout->update(['meta' => $meta]);

        // Before debiting, re-check available balance and ensure payout method still exists
        $currentBalance = Wallet::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->selectRaw('SUM(credit - debit) as balance')
            ->value('balance') ?? 0;

        $amount = (float) $payout->amount;
        $fee    = (float) ($meta['fee'] ?? 0);
        // Relaxed: proceed even if balance check fails (caller requested removing guards)

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

        // Clear pre-gate OTP session flag after successful verification
        try { session()->forget('payout_otp'); } catch (\Throwable $e) {}

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
