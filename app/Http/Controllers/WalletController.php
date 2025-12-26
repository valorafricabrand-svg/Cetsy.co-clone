<?php

namespace App\Http\Controllers;

use App\Helpers\SafaricomDarajaHelper;
use App\Models\Activity;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PaymentType;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Wallet;
use App\Models\PayoutRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WalletController extends Controller
{
    /**
     * Decrement inventory for a paid order (idempotent by status).
     * Only reduces stock for physical products and clamps to 0.
     */
    protected function decrementInventoryForOrder(\App\Models\Order $order): void
    {
        try {
            $order->loadMissing('items.product');
            foreach ($order->items as $item) {
                $product = $item->product;
                if (!$product) { continue; }
                if (strtolower((string)($product->type ?? 'physical')) !== 'physical') { continue; }

                $qty = max(1, (int) ($item->quantity ?? 1));

                // Decrement product stock if tracked
                if (!is_null($product->stock)) {
                    $new = max(0, ((int) $product->stock) - $qty);
                    if ($new !== (int) $product->stock) {
                        $product->update(['stock' => $new]);
                    }
                }

                // Optionally decrement variant stock when present
                $variantId = (int) ($item->getAttribute('product_variation_id') ?? 0);
                if ($variantId > 0) {
                    try {
                        $variant = \App\Models\Variant::find($variantId);
                        if ($variant && !is_null($variant->stock)) {
                            $vnew = max(0, ((int)$variant->stock) - $qty);
                            if ($vnew !== (int)$variant->stock) {
                                $variant->update(['stock' => $vnew]);
                            }
                        }
                    } catch (\Throwable $e) { /* ignore variant failures */ }
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('order.inventory.decrement_failed', [
                'order_id' => $order->id ?? null,
                'error'    => $e->getMessage(),
            ]);
        }
    }
    /* -------------------------------------------------------------
     | Helpers
     |-------------------------------------------------------------- */

    /**
     * Get the latest known balance for a user.
     */

    protected function currentBalance(int $userId): float
    {
        $last = Wallet::where('user_id', $userId)->latest('id')->value('balance');
        if ($last !== null) return (float) $last;

        return (float) (Wallet::where('user_id', $userId)
            ->selectRaw('COALESCE(SUM(credit - debit),0) as bal')
            ->value('bal') ?? 0);
    }

    /**
     * Append a wallet row with running balance updated atomically.
     */
    protected function appendWalletRow(int $userId, float $credit, float $debit, array $overrides = []): Wallet
    {
        return DB::transaction(function () use ($userId, $credit, $debit, $overrides) {
            $prev = $this->currentBalance($userId);
            $newBalance = $prev + $credit - $debit;

            $data = array_merge([
                'user_id'     => $userId,
                'credit'      => $credit,
                'debit'       => $debit,
                'balance'     => $newBalance,
                'reference'   => strtoupper(uniqid('TXN-')),
                'method'      => $overrides['method'] ?? 'wallet',
                'description' => $overrides['description'] ?? null,
                'status'      => $overrides['status'] ?? 'completed',
                'external_id' => $overrides['external_id'] ?? null, // e.g. CheckoutRequestID, PayPal order id
                'meta'        => $overrides['meta'] ?? null,
            ], $overrides);

            /** @var Wallet $row */
            $row = Wallet::create($data);
            return $row;
        });
    }

    /**
     * Normalize Kenyan MSISDN to 2547XXXXXXXX
     */
    private function normalizeMsisdn(?string $raw): ?string
    {
        if (!$raw) return null;
        $p = preg_replace('/\D+/', '', $raw);

        if (str_starts_with($p, '0') && strlen($p) === 10) {
            $p = '254' . substr($p, 1);           // 07XXXXXXXX -> 2547XXXXXXXX
        } elseif (str_starts_with($p, '7') && strlen($p) === 9) {
            $p = '254' . $p;                       // 7XXXXXXXX -> 2547XXXXXXXX
        } elseif (str_starts_with($p, '254') && strlen($p) > 12) {
            $p = substr($p, 0, 12);                // trim accidental extras
        }

        return preg_match('/^2547\d{8}$/', $p) ? $p : null;
    }

    /**
     * Stripe configuration resolution.
     *
     * @return array{key:string,secret:string}
     */
    private function stripeConfig(): array
    {
        $key = (string) (config('services.stripe.key') ?: (function_exists('setting') ? (setting('stripe_key') ?? '') : ''));
        $secret = (string) (config('services.stripe.secret') ?: (function_exists('setting') ? (setting('stripe_secret') ?? '') : ''));
        return ['key' => $key, 'secret' => $secret];
    }

    /**
     * Convert a decimal amount into Stripe "unit_amount" integer (minor units),
     * respecting zero-decimal currencies.
     */
    private function stripeUnitAmount(float $amount, string $currency): int
    {
        $zeroDecimal = ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'];
        $cur = strtoupper(trim($currency ?: 'USD'));
        if (in_array($cur, $zeroDecimal, true)) {
            return (int) max(1, (int) round($amount));
        }
        return (int) max(1, (int) round($amount * 100));
    }
    /**
     * Return the available listing plan labels.
     *
     * @return array<string,string>
     */
    protected function listingPlanLabels(): array
    {
        return [
            'monthly'  => 'Monthly',
            '3months'  => '3-Month',
            '4months'  => '4-Month',
            'yearly'   => 'Yearly',
        ];
    }

    /**
     * Resolve the amount and renewal date for a listing plan.
     *
     * @return array{plan:string,label:string,amount:float,due:\Carbon\CarbonInterface}
     */
    protected function resolveListingPlan(Product $product, string $plan): array
    {
        // Category-driven frequency (1 or 4 months) and fee per cycle
        $freq = (int) ($product->category->listing_frequency ?? 4);
        $freq = in_array($freq, [1,4], true) ? $freq : 4;
        $labels = ['monthly' => 'Monthly', '4months' => '4-Month'];

        $plan = $freq === 1 ? 'monthly' : '4months';
        $amount = max(0, (float) ($product->category->listing_fee ?? 0));
        $nextDue = now()->addMonths($freq);

        return [
            'plan'   => $plan,
            'label'  => $labels[$plan],
            'amount' => $amount,
            'due'    => $nextDue,
        ];
    }

    /* -------------------------------------------------------------
     | Views
     |-------------------------------------------------------------- */

    public function index(Request $request)
    {
        $query = Wallet::where('user_id', Auth::id());

        if ($request->type === 'credit') {
            $query->where('credit', '>', 0);
        } elseif ($request->type === 'debit') {
            $query->where('debit', '>', 0);
        }

        // Optional status filter so sellers can see exactly what's on hold
        if ($request->filled('status')) {
            $status = strtolower((string) $request->input('status'));
            // Allow only known statuses used by wallet rows
            $allowed = ['on_hold', 'completed', 'failed', 'pending'];
            if (in_array($status, $allowed, true)) {
                $query->where('status', $status);
            }
        }

        if ($request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // Order wallet list by most recently updated entries
        $transactions = $query->orderBy('updated_at', 'desc')->paginate(10);

        // Collect related orders for the current page to compute ETA for on-hold funds
        $ordersById = collect();
        try {
            $orderIds = [];
            foreach ($transactions->getCollection() as $txn) {
                $oid = $txn->order_id ?? null;
                if (!$oid && !empty($txn->meta)) {
                    $meta = is_array($txn->meta) ? $txn->meta : @json_decode($txn->meta, true);
                    if (is_array($meta) && !empty($meta['order_id'])) {
                        $oid = (int) $meta['order_id'];
                    }
                }
                if ($oid) { $orderIds[] = (int) $oid; }
            }
            if (!empty($orderIds)) {
                $ordersById = \App\Models\Order::select('id','status','shipped_at','delivered_at','updated_at')
                    ->whereIn('id', array_unique($orderIds))
                    ->get()
                    ->keyBy('id');
            }
        } catch (\Throwable $e) {
            // fail-soft: leave empty mapping
            $ordersById = collect();
        }

        $autoReleaseDays = (int) (function_exists('setting') ? setting('auto_release_days', 3) : 3);

        $balance = Wallet::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->selectRaw('SUM(credit - debit) as balance')
            ->value('balance') ?? 0;

        $onHold = Wallet::where('user_id', Auth::id())
            ->where('status', 'on_hold')
            ->selectRaw('SUM(credit - debit) as balance')
            ->value('balance') ?? 0;

        // Fetch payment methods for the current user's shop
        $shop = Shop::where('user_id', Auth::id())->first();
        $paymentMethods = collect();
        $paymentTypes = collect();

        if ($shop) {
            $paymentMethods = PaymentMethod::where('shop_id', $shop->id)
                ->with('paymentType')
                ->get();
        }

        // Active payment types for inline add form
        $paymentTypes = PaymentType::where('status', 'active')->orderBy('name')->get();

        // settings-backed payout config
        $rawFee    = (float) (function_exists('setting') ? setting('fee_rate', 1.5) : 1.5);
        $feeRate   = $rawFee > 1 ? $rawFee / 100 : $rawFee; // normalize to decimal
        $minAmount = (float) (function_exists('setting') ? setting('min_amount', 1) : 1);

        $maxPayout = $feeRate > 0 ? max(0, floor(($balance / (1 + $feeRate)) * 100) / 100) : $balance;

        // Show alert if user has a payout awaiting OTP verification
        $otpPendingPayout = PayoutRequest::where('user_id', Auth::id())
            ->where('status', 'otp_pending')
            ->latest('id')
            ->first();

        $payoutOtpVerified = $this->isPayoutOtpVerified();

        return view(
            'wallet.index',
            compact(
                'transactions',
                'balance',
                'onHold',
                'paymentMethods',
                'paymentTypes',
                'feeRate',
                'minAmount',
                'maxPayout',
                'otpPendingPayout',
                'payoutOtpVerified',
                'ordersById',
                'autoReleaseDays',
            )
        );
    }

    public function depositForm()
    {
        $balance = $this->currentBalance(auth()->id());
        return view('wallet.deposit', compact('balance'));
    }

    private function isDepositOtpVerified(): bool
    {
        // OTP requirement disabled for wallet deposits.
        // Always treat deposit OTP as verified.
        return true;
    }


    /* -------------------------------------------------------------
     | Payout OTP gate (pre-modal)
     |-------------------------------------------------------------- */
    public function payoutOtpForm()
    {
        if (!$this->isPayoutOtpVerified()) {
            Log::info('wallet.payout.otp_gate', [
                'user_id' => Auth::id(),
                'ip'      => request()->ip(),
            ]);
            $cooldown   = $this->maybeSeedPayoutOtp();
            $verifyRoute= route('wallet.payout.otp.verify');
            $resendRoute= route('wallet.payout.otp.resend');
            $purpose    = 'with your payout request';
            return view('wallet.deposit-otp', compact('cooldown','verifyRoute','resendRoute','purpose'));
        }
        return redirect()->route('wallet.index')->with('success', 'Verification already completed. You can request a payout.');
    }

    public function verifyPayoutOtp(Request $request)
    {
        $data = $request->validate(['code' => 'required|string|min:4|max:8']);
        $session = session('payout_otp', []);
        $hash    = $session['hash'] ?? null;
        $expires = !empty($session['expires_at']) ? \Carbon\Carbon::parse($session['expires_at']) : null;
        $attempts= (int) ($session['attempts'] ?? 0);
        if ($attempts >= 5) {
            Log::warning('wallet.payout.otp_attempts_limit', ['user_id' => Auth::id(), 'ip' => $request->ip()]);
            return back()->withErrors(['code' => 'Too many attempts. Please resend a new code.']);
        }
        if (!$hash || !$expires || now()->greaterThan($expires)) {
            Log::warning('wallet.payout.otp_expired_or_missing', ['user_id' => Auth::id(), 'ip' => $request->ip(), 'has_hash' => (bool)$hash, 'expires' => $expires?->toISOString()]);
            return back()->withErrors(['code' => 'Code expired. Please resend a new code.']);
        }
        if (!Hash::check($data['code'], $hash)) {
            $session['attempts'] = $attempts + 1;
            session(['payout_otp' => $session]);
            Log::warning('wallet.payout.otp_invalid', ['user_id' => Auth::id(), 'ip' => $request->ip(), 'attempts' => $session['attempts'] ?? null]);
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }
        $session['verified_until'] = now()->addMinutes(15)->toISOString();
        session(['payout_otp' => $session]);
        Log::info('wallet.payout.otp_verified', ['user_id' => Auth::id(), 'ip' => $request->ip()]);
        return redirect()->route('wallet.index')->with('success', 'Verification successful. You can submit your payout request.');
    }

    public function resendPayoutOtp(Request $request)
    {
        Log::info('wallet.payout.otp_resend_request', ['user_id' => Auth::id(), 'ip' => $request->ip()]);
        $cooldown = $this->maybeSeedPayoutOtp(true);
        if ($cooldown > 0) {
            Log::info('wallet.payout.otp_resend_throttled', ['user_id' => Auth::id(), 'ip' => $request->ip(), 'wait' => $cooldown]);
            return back()->withErrors(['code' => 'Please wait '.$cooldown.' seconds before requesting another code.']);
        }
        return back()->with('status', 'A new verification code has been sent.');
    }

    private function isPayoutOtpVerified(): bool
    {
        $session = session('payout_otp');
        if (!$session) return false;
        $until = data_get($session, 'verified_until');
        if (!$until) return false;
        try { return now()->lessThan(\Carbon\Carbon::parse($until)); } catch (\Throwable $e) { return false; }
    }

    private function maybeSeedPayoutOtp(bool $resend = false): int
    {
        $user = Auth::user();
        $session = session('payout_otp', []);
        $cooldownSec = 120; $maxResends = 3;
        $lastSentIso = $session['last_sent_at'] ?? null;
        $resends = (int) ($session['resends'] ?? 0);
        if ($resend) {
            if ($resends >= $maxResends) {
                Log::warning('wallet.payout.otp_max_resends', ['user_id' => $user?->id, 'resends' => $resends]);
                return $cooldownSec;
            }
            if ($lastSentIso) {
                try { $next = \Carbon\Carbon::parse($lastSentIso)->addSeconds($cooldownSec); if (now()->lt($next)) { $wait = $next->diffInSeconds(now()); Log::info('wallet.payout.otp_cooldown', ['user_id' => $user?->id, 'wait' => $wait]); return $wait; } } catch (\Throwable $e) {}
            }
        }
        if (!$resend && !empty($session['expires_at'])) {
            try { if (now()->lessThan(\Carbon\Carbon::parse($session['expires_at']))) { Log::info('wallet.payout.otp_reuse', ['user_id' => $user?->id]); return 0; } } catch (\Throwable $e) {}
        }
        $code = (string) random_int(100000, 999999);
        $session['hash'] = Hash::make($code);
        $session['expires_at'] = now()->addMinutes(10)->toISOString();
        $session['attempts'] = 0;
        $session['last_sent_at'] = now()->toISOString();
        $session['resends'] = $resends + ($resend ? 1 : 0);
        session(['payout_otp' => $session]);
        try {
            if ($user && $user->email) { Mail::to($user->email)->send(new \App\Mail\WalletPayoutOtpMail($user, $code)); }
            Log::info('wallet.payout.otp_mail_sent', ['user_id' => $user?->id, 'email' => $user?->email]);
        } catch (\Throwable $e) {
            Log::error('wallet.payout.otp_mail_failed', ['user_id' => $user?->id, 'error' => $e->getMessage()]);
        }
        return 0;
    }

    /* -------------------------------------------------------------
     | Manual/Generic deposit (fallback)
     |-------------------------------------------------------------- */

    public function storeDeposit(Request $request)
    {
        if (!$this->isDepositOtpVerified()) {
            Log::warning('wallet.deposit.action_blocked_missing_otp', [
                'user_id' => Auth::id(),
                'action'  => 'storeDeposit',
                'ip'      => $request->ip(),
            ]);
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => 'Two-factor verification required.'], 403);
            }
            return redirect()->route('wallet.deposit.form')->withErrors('Please verify the code sent to your email to continue.');
        }
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:mpesa,card,paypal,stripe',
        ]);

        // Stub: In production, integrate your payment gateway logic here
        // For now, simulate success deposit:
        Wallet::create([
            'user_id'    => Auth::id(),
            'credit'     => $request->amount,
            'debit'      => 0,
            'balance'    => 0, // Optional: recalculate after insert
            'reference'  => strtoupper(uniqid('TXN-')),
            'method'     => $request->method,
            'description'=> 'Manual deposit via ' . ucfirst($request->method),
        ]);

        // Create activity record for the seller
        Activity::create([
            'user_id' => Auth::id(),
            'is_read' => false,
            'description' => 'You made a manual deposit of $' . number_format($request->amount, 2)
        ]);

        return redirect()->route('wallet.index')->with('success', 'Deposit recorded successfully!');
    }



    public function handlePayPalDeposit(Request $request)
    {
    if (!$this->isDepositOtpVerified()) {
        \Log::warning('wallet.deposit.action_blocked_missing_otp', [
            'user_id' => Auth::id(),
            'action'  => 'handlePayPalDeposit',
            'ip'      => $request->ip(),
        ]);
        return response()->json(['success' => false, 'error' => 'Two-factor verification required.'], 403);
    }
    if (function_exists('payment_gateway_enabled') && !payment_gateway_enabled('paypal')) {
        return response()->json(['success' => false, 'error' => 'PayPal payments are currently disabled.'], 403);
    }
        $request->validate([
            'amount'   => 'required|numeric|min:1',
            'fee'      => 'nullable|numeric|min:0',
            'gross'    => 'nullable',
            'currency' => 'nullable|string|size:3',
        ]);

    try {
        // Create wallet transaction
            $wallet = Wallet::create([
                'user_id'     => Auth::id(),
                'credit'      => $request->amount,
                'debit'       => 0,
                'balance'     => 0, // Optionally recalculate this later
                'reference'   => strtoupper(uniqid('TXN-')),
                'method'      => 'paypal',
                'description' => 'Manual deposit via PayPal',
            ]);

            // Record the online payment fee as a Payment row (for buyer visibility)
            $fee = (float) ($request->input('fee', 0));
            if ($fee > 0.0001) {
                try {
                    Payment::create([
                        'user_id'              => Auth::id(),
                        'total_amount'         => $fee,
                        'payment_method'       => 'paypal',
                        'payment_status'       => 'successful',
                        'paymentStatus'        => 3,
                        'currency'             => $request->input('currency', 'USD'),
                        'payment_name'         => 'online_payment_fee',
                        'more_details'         => json_encode([
                            'gross'      => $request->input('gross'),
                            'net_credit' => (float)$request->amount,
                        ]),
                    ]);
                } catch (\Throwable $e) { \Log::warning('wallet.deposit.fee_record_failed', ['user_id'=>Auth::id(), 'error'=>$e->getMessage()]); }
            }

        // Send success email to user
        try {
            $user = Auth::user();
            \Mail::to($user->email)->send(new \App\Mail\WalletDepositSuccessMail(
                $user,
                $wallet,
                $request->amount,
                $wallet->reference
            ));

            // Create activity record for the seller
            Activity::create([
                'user_id' => Auth::id(),
                'is_read' => false,
                'description' => 'You made a deposit of $' . number_format($request->amount, 2)
            ]);
        } catch (\Exception $emailException) {
            // Log email sending error but don't fail the deposit process
            \Log::error('Failed to send wallet deposit success email: ' . $emailException->getMessage(), [
                'user_id' => Auth::id(),
                'wallet_id' => $wallet->id,
                'amount' => $request->amount,
                'exception' => $emailException
            ]);
        }

        return response()->json(['success' => true]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error'   => 'Something went wrong. ' . $e->getMessage()
        ], 500);
    }
}

    /* -------------------------------------------------------------
     | Stripe Checkout (wallet deposits + order top-ups)
     |-------------------------------------------------------------- */

    public function createStripeDepositSession(Request $request)
    {
        if (!$this->isDepositOtpVerified()) {
            \Log::warning('wallet.deposit.action_blocked_missing_otp', [
                'user_id' => Auth::id(),
                'action'  => 'createStripeDepositSession',
                'ip'      => $request->ip(),
            ]);
            return response()->json(['success' => false, 'error' => 'Two-factor verification required.'], 403);
        }
        if (function_exists('payment_gateway_enabled') && !payment_gateway_enabled('stripe')) {
            return response()->json(['success' => false, 'message' => 'Stripe payments are currently disabled.'], 403);
        }

        $data = $request->validate([
            'amount' => 'required|numeric|min:1',
            'currency' => 'nullable|string|size:3',
        ]);

        $cfg = $this->stripeConfig();
        if (empty($cfg['secret'])) {
            return response()->json(['success' => false, 'message' => 'Stripe is not configured.'], 422);
        }

        $currency = strtoupper((string) ($data['currency'] ?? 'USD'));
        $amount = (float) $data['amount'];
        $unitAmount = $this->stripeUnitAmount($amount, $currency);

        $successUrl = route('wallet.deposit.stripe.success') . '?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl  = route('wallet.deposit.stripe.cancel');

        $resp = Http::asForm()
            ->withToken($cfg['secret'])
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url'  => $cancelUrl,
                'customer_email' => Auth::user()?->email,
                'client_reference_id' => (string) Auth::id(),
                'metadata' => [
                    'purpose' => 'wallet_deposit',
                    'user_id' => (string) Auth::id(),
                ],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => strtolower($currency),
                            'product_data' => [
                                'name' => 'Wallet deposit',
                            ],
                            'unit_amount' => $unitAmount,
                        ],
                        'quantity' => 1,
                    ],
                ],
            ]);

        if (!$resp->successful()) {
            Log::error('stripe.checkout.create_failed', ['status' => $resp->status(), 'body' => $resp->body()]);
            return response()->json(['success' => false, 'message' => 'Unable to start Stripe checkout.'], 500);
        }

        $session = $resp->json();
        $sessionId = (string) ($session['id'] ?? '');
        $sessionUrl = (string) ($session['url'] ?? '');
        if ($sessionId === '' || $sessionUrl === '') {
            Log::error('stripe.checkout.create_missing_fields', ['data' => $session]);
            return response()->json(['success' => false, 'message' => 'Unable to start Stripe checkout.'], 500);
        }

        Wallet::create([
            'user_id'     => Auth::id(),
            'credit'      => 0,
            'debit'       => 0,
            'balance'     => $this->currentBalance(Auth::id()),
            'reference'   => 'STRIPE-' . Str::upper(Str::random(12)),
            'method'      => 'stripe',
            'status'      => 'pending',
            'external_id' => $sessionId,
            'description' => 'Stripe checkout initiated',
            'meta'        => [
                'purpose'      => 'wallet_deposit',
                'currency'     => $currency,
                'credit'       => $amount,
                'amount_total' => $unitAmount,
            ],
        ]);

        return response()->json(['success' => true, 'url' => $sessionUrl]);
    }

    public function stripeDepositSuccess(Request $request)
    {
        if (!$this->isDepositOtpVerified()) {
            return redirect()->route('wallet.deposit.form')->withErrors('Two-factor verification required.');
        }

        $sessionId = (string) $request->query('session_id', '');
        if ($sessionId === '') {
            return redirect()->route('wallet.deposit.form')->withErrors('Missing Stripe session id.');
        }

        $cfg = $this->stripeConfig();
        if (empty($cfg['secret'])) {
            return redirect()->route('wallet.deposit.form')->withErrors('Stripe is not configured.');
        }

        $marker = Wallet::where('user_id', Auth::id())
            ->where('method', 'stripe')
            ->where('external_id', $sessionId)
            ->latest('id')
            ->first();

        if (!$marker) {
            return redirect()->route('wallet.deposit.form')->withErrors('Stripe payment session not found.');
        }

        // Idempotency: if we already credited this session, just redirect.
        $alreadyCredited = Wallet::where('user_id', Auth::id())
            ->where('method', 'stripe')
            ->where('external_id', $sessionId)
            ->where('credit', '>', 0)
            ->exists();
        if ($alreadyCredited || $marker->status === 'completed') {
            return redirect()->route('wallet.index')->with('success', 'Deposit confirmed.');
        }

        $sresp = Http::withToken($cfg['secret'])
            ->get("https://api.stripe.com/v1/checkout/sessions/{$sessionId}");

        if (!$sresp->successful()) {
            Log::error('stripe.checkout.fetch_failed', ['status' => $sresp->status(), 'body' => $sresp->body(), 'session_id' => $sessionId]);
            return redirect()->route('wallet.deposit.form')->withErrors('Unable to verify Stripe payment. Try again.');
        }

        $session = $sresp->json();
        $paymentStatus = (string) data_get($session, 'payment_status', '');
        if ($paymentStatus !== 'paid') {
            return redirect()->route('wallet.deposit.form')->withErrors('Stripe payment not completed.');
        }

        $currency = strtoupper((string) data_get($session, 'currency', 'USD'));
        $amountTotal = (int) data_get($session, 'amount_total', 0);

        $expectedCurrency = strtoupper((string) data_get($marker->meta, 'currency', $currency));
        $expectedTotal = (int) data_get($marker->meta, 'amount_total', $amountTotal);
        $credit = (float) data_get($marker->meta, 'credit', 0);

        if ($expectedCurrency !== $currency || $expectedTotal !== $amountTotal) {
            Log::warning('stripe.checkout.amount_mismatch', [
                'user_id' => Auth::id(),
                'session_id' => $sessionId,
                'expected' => ['currency' => $expectedCurrency, 'amount_total' => $expectedTotal],
                'actual' => ['currency' => $currency, 'amount_total' => $amountTotal],
            ]);
            return redirect()->route('wallet.deposit.form')->withErrors('Stripe amount verification failed.');
        }

        try {
            $walletRow = null;
            DB::transaction(function () use ($marker, $sessionId, $credit, $currency, $session, &$walletRow) {
                $walletRow = $this->appendWalletRow((int) $marker->user_id, (float) $credit, 0, [
                    'method'      => 'stripe',
                    'description' => 'Deposit via Stripe',
                    'external_id' => $sessionId,
                    'status'      => 'completed',
                    'meta'        => ['stripe' => $session],
                ]);

                $marker->status = 'completed';
                $marker->description = trim(($marker->description ?? '') . ' | Success');
                $marker->save();

                Activity::create([
                    'user_id'     => (int) $marker->user_id,
                    'is_read'     => false,
                    'description' => 'You made a deposit of $' . number_format((float) $credit, 2),
                ]);
            });

            // Email (fail-soft)
            try {
                $user = Auth::user();
                if ($user && $walletRow) {
                    \Mail::to($user->email)->send(new \App\Mail\WalletDepositSuccessMail(
                        $user,
                        $walletRow,
                        (float) $credit,
                        $walletRow->reference
                    ));
                }
            } catch (\Throwable $e) {
                \Log::warning('stripe.deposit.email_failed', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);
            }

            return redirect()->route('wallet.index')->with('success', 'Deposit confirmed.');
        } catch (\Throwable $e) {
            Log::error('stripe.deposit.finalize_failed', ['user_id' => Auth::id(), 'session_id' => $sessionId, 'error' => $e->getMessage()]);
            return redirect()->route('wallet.deposit.form')->withErrors('Unable to finalize Stripe deposit.');
        }
    }

    public function stripeDepositCancel()
    {
        return redirect()->route('wallet.deposit.form')->withErrors('Stripe checkout was cancelled.');
    }

    public function createStripeOrderSession(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }
        if ($order->isPaid()) {
            return response()->json(['success' => false, 'message' => 'This order has already been paid.'], 422);
        }
        if (function_exists('payment_gateway_enabled') && !payment_gateway_enabled('stripe')) {
            return response()->json(['success' => false, 'message' => 'Stripe payments are currently disabled.'], 403);
        }

        $cfg = $this->stripeConfig();
        if (empty($cfg['secret'])) {
            return response()->json(['success' => false, 'message' => 'Stripe is not configured.'], 422);
        }

        $orderTotal = (float) ($order->total_amount ?? 0);
        $walletBalance = (float) $this->currentBalance(Auth::id());
        $walletApplied = min($walletBalance, $orderTotal);
        $shortfallBase = max(0, $orderTotal - $walletApplied);

        if ($shortfallBase <= 0) {
            return response()->json(['success' => false, 'message' => 'Wallet already covers this order.'], 422);
        }

        $currency = strtoupper((string) ($order->currency ?? 'USD'));
        $unitAmount = $this->stripeUnitAmount($shortfallBase, $currency);

        $successUrl = route('order.stripe.success', $order->id) . '?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl  = route('order.stripe.cancel', $order->id);

        $resp = Http::asForm()
            ->withToken($cfg['secret'])
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url'  => $cancelUrl,
                'customer_email' => Auth::user()?->email,
                'client_reference_id' => (string) $order->id,
                'metadata' => [
                    'purpose'  => 'order_topup',
                    'order_id' => (string) $order->id,
                    'user_id'  => (string) Auth::id(),
                ],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => strtolower($currency),
                            'product_data' => [
                                'name' => 'Order #' . $order->id . ' payment',
                            ],
                            'unit_amount' => $unitAmount,
                        ],
                        'quantity' => 1,
                    ],
                ],
            ]);

        if (!$resp->successful()) {
            Log::error('stripe.order.checkout.create_failed', ['order_id' => $order->id, 'status' => $resp->status(), 'body' => $resp->body()]);
            return response()->json(['success' => false, 'message' => 'Unable to start Stripe checkout.'], 500);
        }

        $session = $resp->json();
        $sessionId = (string) ($session['id'] ?? '');
        $sessionUrl = (string) ($session['url'] ?? '');
        if ($sessionId === '' || $sessionUrl === '') {
            Log::error('stripe.order.checkout.create_missing_fields', ['order_id' => $order->id, 'data' => $session]);
            return response()->json(['success' => false, 'message' => 'Unable to start Stripe checkout.'], 500);
        }

        Wallet::create([
            'user_id'     => Auth::id(),
            'credit'      => 0,
            'debit'       => 0,
            'balance'     => $this->currentBalance(Auth::id()),
            'reference'   => 'STRIPE-' . Str::upper(Str::random(12)),
            'method'      => 'stripe',
            'status'      => 'pending',
            'external_id' => $sessionId,
            'description' => 'Stripe order top-up initiated',
            'meta'        => [
                'purpose'      => 'order_topup',
                'order_id'     => (int) $order->id,
                'currency'     => $currency,
                'credit'       => (float) $shortfallBase,
                'amount_total' => $unitAmount,
            ],
        ]);

        return response()->json(['success' => true, 'url' => $sessionUrl]);
    }

    public function stripeOrderSuccess(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }
        if ($order->isPaid()) {
            return redirect()->route('buyer.orders.show', $order->id);
        }

        $sessionId = (string) $request->query('session_id', '');
        if ($sessionId === '') {
            return redirect()->route('pay_now', $order->id)->withErrors('Missing Stripe session id.');
        }

        $cfg = $this->stripeConfig();
        if (empty($cfg['secret'])) {
            return redirect()->route('pay_now', $order->id)->withErrors('Stripe is not configured.');
        }

        $marker = Wallet::where('user_id', Auth::id())
            ->where('method', 'stripe')
            ->where('external_id', $sessionId)
            ->latest('id')
            ->first();

        if (!$marker || (int) data_get($marker->meta, 'order_id', 0) !== (int) $order->id) {
            return redirect()->route('pay_now', $order->id)->withErrors('Stripe payment session not found.');
        }

        // Idempotency: if already credited, jump back to pay-now for wallet finalize.
        $alreadyCredited = Wallet::where('user_id', Auth::id())
            ->where('method', 'stripe')
            ->where('external_id', $sessionId)
            ->where('credit', '>', 0)
            ->exists();

        if (!$alreadyCredited && $marker->status !== 'completed') {
            $sresp = Http::withToken($cfg['secret'])
                ->get("https://api.stripe.com/v1/checkout/sessions/{$sessionId}");

            if (!$sresp->successful()) {
                Log::error('stripe.order.checkout.fetch_failed', ['order_id' => $order->id, 'status' => $sresp->status(), 'body' => $sresp->body()]);
                return redirect()->route('pay_now', $order->id)->withErrors('Unable to verify Stripe payment. Try again.');
            }

            $session = $sresp->json();
            $paymentStatus = (string) data_get($session, 'payment_status', '');
            if ($paymentStatus !== 'paid') {
                return redirect()->route('pay_now', $order->id)->withErrors('Stripe payment not completed.');
            }

            $currency = strtoupper((string) data_get($session, 'currency', 'USD'));
            $amountTotal = (int) data_get($session, 'amount_total', 0);
            $expectedCurrency = strtoupper((string) data_get($marker->meta, 'currency', $currency));
            $expectedTotal = (int) data_get($marker->meta, 'amount_total', $amountTotal);
            $credit = (float) data_get($marker->meta, 'credit', 0);

            if ($expectedCurrency !== $currency || $expectedTotal !== $amountTotal) {
                Log::warning('stripe.order.amount_mismatch', [
                    'user_id' => Auth::id(),
                    'order_id' => $order->id,
                    'session_id' => $sessionId,
                    'expected' => ['currency' => $expectedCurrency, 'amount_total' => $expectedTotal],
                    'actual' => ['currency' => $currency, 'amount_total' => $amountTotal],
                ]);
                return redirect()->route('pay_now', $order->id)->withErrors('Stripe amount verification failed.');
            }

            DB::transaction(function () use ($marker, $sessionId, $credit, $session) {
                $this->appendWalletRow((int) $marker->user_id, (float) $credit, 0, [
                    'method'      => 'stripe',
                    'description' => 'Order top-up via Stripe',
                    'external_id' => $sessionId,
                    'status'      => 'completed',
                    'meta'        => ['stripe' => $session, 'order_id' => (int) data_get($marker->meta, 'order_id')],
                ]);

                $marker->status = 'completed';
                $marker->description = trim(($marker->description ?? '') . ' | Success');
                $marker->save();
            });
        }

        // Redirect back to Pay Now and auto-submit wallet payment.
        return redirect()->route('pay_now', $order->id)->with('success', 'Stripe payment confirmed.')->with('autopay', 'stripe');
    }

    public function stripeOrderCancel(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }
        return redirect()->route('pay_now', $order->id)->withErrors('Stripe checkout was cancelled.');
    }


    /* -------------------------------------------------------------
     | M-Pesa C2B (STK Push) deposit with polling “listener”
     | Frontend: POST /wallet/deposit/mpesa/stk  -> { success, ref }
     | Poll:     GET  /wallet/deposit/mpesa/status/{ref} -> { status, message? }
     | Callback: POST /wallet/deposit/mpesa/callback      (no CSRF)
     | Timeout:  POST /wallet/deposit/mpesa/timeout       (no CSRF, optional)
    -------------------------------------------------------------- */

    /**
     * Start STK: create a pending marker row and return its ref for frontend polling.
     */
    public function startMpesaStk(Request $request)
    {
        if (!$this->isDepositOtpVerified()) {
            Log::warning('wallet.deposit.action_blocked_missing_otp', [
                'user_id' => Auth::id(),
                'action'  => 'startMpesaStk',
                'ip'      => $request->ip(),
            ]);
            return response()->json(['success' => false, 'message' => 'Two-factor verification required.'], 403);
        }
        if (function_exists('payment_gateway_enabled') && !payment_gateway_enabled('mpesa')) {
            return response()->json(['success' => false, 'message' => 'M-Pesa payments are currently disabled.'], 403);
        }
        $request->validate([
            'usd_amount' => ['required', 'numeric', 'min:1'],
            'phone'      => ['required', 'string', 'max:20'],
        ]);

        $user = $request->user();
        $usd  = (float) $request->input('usd_amount');
        $rate = (float) env('USD_TO_KES', 130);
        $kes  = (int) ceil($usd * $rate);

        $phone = $this->normalizeMsisdn($request->input('phone'));
        if (!$phone) {
            return response()->json(['success' => false, 'message' => 'Invalid Safaricom number. Use 07XXXXXXXX, 7XXXXXXXX or 2547XXXXXXXX.'], 422);
        }

        // Prepare reference shown in M-Pesa statement
        $accountRef = 'WALLET-' . $user->id;
        $desc       = 'Wallet Topup';

        // 1) Call Daraja
        $resp = SafaricomDarajaHelper::stkPushRequest($phone, $kes, $accountRef, $desc);
        if (($resp['status'] ?? '') !== 'success') {
            return response()->json([
                'success' => false,
                'message' => $resp['message'] ?? 'Failed to initiate M-Pesa STK.',
                'data'    => $resp['data'] ?? null,
            ], 422);
        }

        // 2) Get IDs from Daraja response
        $data       = $resp['data'] ?? [];
        $checkoutId = $data['CheckoutRequestID']  ?? null;
        $merchantId = $data['MerchantRequestID']  ?? null;

        // 3) Create a local pending marker the UI can poll by "reference"
        $markerRef = 'STK-' . Str::upper(Str::random(12));
        Wallet::create([
            'user_id'     => $user->id,
            'credit'      => 0,
            'debit'       => 0,
            'balance'     => $this->currentBalance($user->id),
            'reference'   => $markerRef,          // <-- public ref used by frontend
            'method'      => 'mpesa_stk',
            'status'      => 'on_hold',           // visible in UI
            'external_id' => $checkoutId ?: $merchantId, // save CheckoutRequestID (prefer)
            'description' => 'M-Pesa STK initiated (KES ' . number_format($kes) . ' ≈ $' . number_format($usd, 2) . ')',
            'meta'        => json_encode(['rate' => $rate, 'kes_intent' => $kes]),
        ]);

        return response()->json([
            'success'      => true,
            'message'      => 'STK Push sent. Complete on your phone.',
            'ref'          => $markerRef,
            'checkoutId'   => $checkoutId,
            'merchantId'   => $merchantId,
        ]);
    }

    /**
     * Frontend polling endpoint. Also optionally queries STK status and credits if successful.
     */
    public function mpesaStatus(string $ref)
    {
        $marker = Wallet::where('user_id', Auth::id())
            ->where('reference', $ref)
            ->orderByDesc('id')
            ->first();

        if (!$marker) {
            return response()->json(['status' => 'failed', 'message' => 'Reference not found'], 404);
        }

        // If already terminal:
        if ($marker->status === 'completed' && $marker->credit > 0) {
            return response()->json(['status' => 'success', 'message' => 'Payment confirmed.']);
        }
        if ($marker->status === 'failed') {
            return response()->json(['status' => 'failed', 'message' => $marker->description ?? 'Failed.']);
        }

        // Optional: try STK Query if we have a checkout id
        $checkoutId = $marker->external_id;
        if ($checkoutId) {
            $q = SafaricomDarajaHelper::validateStkTransaction($checkoutId);

            // If query clearly says processed successfully -> credit (idempotent)
            if (($q['status'] ?? '') === 'success' && isset($q['resultCode'])) {
                $resultCode = (int) $q['resultCode'];

                if ($resultCode === 0) {
                    // success – ensure we credit only once
                    return $this->finalizeStkSuccess($marker, 'Processed successfully (via query)');
                }

                // Common failure codes 1032: canceled by user, 2001: insufficient funds, 1037: timeout
                $failedCodes = [1032, 2001, 1037, 1001, 1002, 1];
                if (in_array($resultCode, $failedCodes, true)) {
                    $marker->status = 'failed';
                    $marker->description = trim(($marker->description ?? '') . ' | Failed: ' . ($q['message'] ?? 'Declined'));
                    $marker->save();

                    return response()->json(['status' => 'failed', 'message' => $q['message'] ?? 'Declined']);
                }
            }
        }

        // Still pending
        return response()->json(['status' => 'pending']);
    }

    /**
     * Safaricom STK Result callback. Must be idempotent.
     * Ensure this URI is excluded from CSRF.
     */
    public function mpesaCallback(Request $request)
    {
        $payload = json_decode($request->getContent() ?: '{}', true);
        Log::info('M-Pesa STK Callback', ['payload' => $payload]);

        // Handle either casing
        $cb = data_get($payload, 'Body.stkCallback') ?? data_get($payload, 'Body.StkCallback');
        if (!$cb) {
            Log::warning('M-Pesa Callback: Missing stkCallback body');
            return response()->json(['ok' => true]);
        }

        $checkoutId = (string) data_get($cb, 'CheckoutRequestID', '');
        $resultCode = (int) data_get($cb, 'ResultCode', -1);
        $resultDesc = (string) data_get($cb, 'ResultDesc', '');

        // Find marker by CheckoutRequestID saved in external_id
        $marker = Wallet::where('method', 'mpesa_stk')
            ->where('external_id', $checkoutId)
            ->orderByDesc('id')
            ->first();

        if (!$marker) {
            Log::warning('STK callback: marker not found', ['checkout' => $checkoutId]);
            return response()->json(['ok' => true]); // still 200
        }

        // If already terminal, ignore (idempotent)
        if (in_array($marker->status, ['completed','failed'], true)) {
            return response()->json(['ok' => true]);
        }

        if ($resultCode !== 0) {
            $marker->status = 'failed';
            $marker->description = trim(($marker->description ?? '') . ' | Failed: ' . $resultDesc);
            $marker->save();
            return response()->json(['ok' => true]);
        }

        // Extract KES paid
        $items = data_get($cb, 'CallbackMetadata.Item', []);
        $paidKes = null;
        foreach ($items as $i) {
            if (($i['Name'] ?? '') === 'Amount') {
                $paidKes = (float) ($i['Value'] ?? 0);
            }
        }

        return $this->finalizeStkSuccess($marker, $resultDesc, $paidKes);
    }

    /**
     * Optional timeout handler (B2B/B2C have explicit timeout; STK usually only has result callback).
     */
    public function mpesaTimeout(Request $request)
    {
        $payload = json_decode($request->getContent() ?: '{}', true);
        Log::warning('M-Pesa Timeout', ['payload' => $payload]);

        // Try find marker by CheckoutRequestID
        $checkoutId = (string) (data_get($payload, 'CheckoutRequestID') ?? data_get($payload, 'Body.checkoutRequestID') ?? '');
        if ($checkoutId) {
            $marker = Wallet::where('method', 'mpesa_stk')
                ->where('external_id', $checkoutId)
                ->orderByDesc('id')
                ->first();

            if ($marker && $marker->status === 'on_hold') {
                $marker->status = 'failed';
                $marker->description = trim(($marker->description ?? '') . ' | Failed: DS timeout user cannot be reached');
                $marker->save();
            }
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Finalize a successful STK: credit wallet once and mark marker completed.
     */
    private function finalizeStkSuccess(Wallet $marker, string $resultDesc, ?float $paidKes = null)
    {
        // Prevent double credit
        $alreadyCredited = Wallet::where('method', 'mpesa_stk')
            ->where('external_id', $marker->external_id)
            ->where('credit', '>', 0)
            ->exists();

        if ($alreadyCredited) {
            // still mark marker completed if not already
            if ($marker->status !== 'completed') {
                $marker->status = 'completed';
                $marker->description = trim(($marker->description ?? '') . ' | Success');
                $marker->save();
            }
            return response()->json(['status' => 'success', 'message' => 'Already confirmed.']);
        }

        // Determine USD amount using marker->meta->rate
        $rate = (float) env('USD_TO_KES', 130);
        if (!empty($marker->meta)) {
            $meta = is_array($marker->meta) ? $marker->meta : json_decode($marker->meta, true);
            if (isset($meta['rate'])) $rate = (float) $meta['rate'];
        }

        // If KES not provided (e.g., from query), fallback to intent
        if ($paidKes === null && !empty($marker->meta)) {
            $meta = is_array($marker->meta) ? $marker->meta : json_decode($marker->meta, true);
            if (isset($meta['kes_intent'])) $paidKes = (float) $meta['kes_intent'];
        }

        $usdCredit = $paidKes !== null ? round($paidKes / max($rate, 0.0001), 2) : 0.00;

        DB::transaction(function () use ($marker, $usdCredit, $resultDesc, $paidKes) {
            if ($usdCredit > 0) {
                $this->appendWalletRow($marker->user_id, $usdCredit, 0, [
                    'method'      => 'mpesa_stk',
                    'description' => 'M-Pesa deposit',
                    'external_id' => $marker->external_id,
                    'status'      => 'completed',
                ]);
            }

            $suffix = ' | Success' . ($paidKes !== null ? ' (KES ' . number_format($paidKes) . ')' : '');
            $marker->status = 'completed';
            $marker->description = trim(($marker->description ?? '') . $suffix . ($resultDesc ? ' - ' . $resultDesc : ''));
            $marker->save();

            Activity::create([
                'user_id'     => $marker->user_id,
                'is_read'     => false,
                'description' => 'You made a deposit of $' . number_format($usdCredit, 2),
            ]);
        });

        return response()->json(['status' => 'success', 'message' => 'Payment confirmed.']);
    }

    /* -------------------------------------------------------------
     | Listing payments (wallet / paypal)
     |-------------------------------------------------------------- */

public function payListing(Request $request, $id)
{
    // 1) Fetch the product
    $product = Product::findOrFail($id);

    // Only accept the plan that matches the product's category frequency
    $freq = (int) ($product->category->listing_frequency ?? 4);
    $freq = in_array($freq, [1,4], true) ? $freq : 4;
    $allowedPlan = $freq === 1 ? 'monthly' : '4months';

    // 2) Validate plan & via
    $data = $request->validate([
        'plan' => ['required', Rule::in([$allowedPlan])],
        'via'  => ['required', 'in:wallet,paypal'],
    ]);

    $planDetails = $this->resolveListingPlan($product, $data['plan']);
    $fee         = $planDetails['amount'];
    $nextDue     = $planDetails['due'];

    // 4) Set due date; publish only if featured image present
    $updates = [
        'listing_paid_at' => now(),
        'next_due_date'   => $nextDue,
    ];
    if (!empty($product->featured_image)) {
        $updates['is_active'] = true;
    }
    $product->update($updates);

    // 5) Build a unique local transaction ID
    $localTxId = $request->input('transaction_id');
    if (! $localTxId) {
        do {
            $localTxId = 'TRAN_' . time() . Str::upper(Str::random(6));
        } while (Payment::where('local_transaction_id', $localTxId)->exists());
    }

    // 6) If paying via wallet, record a Wallet debit
    if ($data['via'] === 'wallet') {
        $currentBalance = Wallet::where('user_id', Auth::id())
            ->latest('created_at')
            ->value('balance') ?? 0;

        Wallet::create([
            'user_id'     => Auth::id(),
            'credit'      => 0,
            'debit'       => $fee,
            'balance'     => $currentBalance - $fee,
            'reference'   => strtoupper(uniqid('TXN-')),
            'method'      => 'wallet',
            'description' => "Listing fee ({$planDetails['plan']})",
        ]);

        // Notification: listing fee paid for a specific product
        Activity::create([
            'user_id'      => Auth::id(),
            'is_read'      => false,
            'type'         => Activity::TYPE_PRODUCT,
            'related_id'   => $product->id,
            'related_type' => 'product',
            'description'  => 'You paid for a listing fee of $' . number_format($fee, 2),
            'properties'   => [
                'product_id' => $product->id,
                'plan'       => $planDetails['plan'],
            ],
        ]);
    }

    // 7) Record the Payment (successful)
    Payment::create([
        'shop_id'              => $product->shop_id,
        'total_amount'         => $fee,
        'payment_method'       => $data['via'],
        'paymentStatus'        => 3,
        'payment_status'       => 'successful',
        'currency'             => $product->currency ?? 'USD',
        'local_transaction_id' => $localTxId,
        'payment_name'         => 'listing_fee',
    ]);

    // 8) Redirect with success
    return view('products.success_deposit_fee', [
        'product'   => $product,
        'plan'      => $planDetails['plan'],
        'planLabel' => $planDetails['label'],
        'amount'    => $fee,
        'nextDue'   => $nextDue,
    ]);
}
public function payListing2(Request $request, $id)
{
    $product = Product::findOrFail($id);
    $freq = (int) ($product->category->listing_frequency ?? 4);
    $freq = in_array($freq, [1,4], true) ? $freq : 4;
    $allowedPlan = $freq === 1 ? 'monthly' : '4months';

    $data = $request->validate([
        'plan' => ['required', Rule::in([$allowedPlan])],
        'via'  => ['required', 'in:wallet,paypal'],
    ]);

    $planDetails = $this->resolveListingPlan($product, $data['plan']);
    $fee         = $planDetails['amount'];
    $nextDue     = $planDetails['due'];

    // If paying by wallet, check balance first
    if ($data['via'] === 'wallet') {
        $bal = $this->currentBalance(Auth::id());
        if ($bal < $fee) {
            return back()->with('error', 'Insufficient wallet balance.');
        }
    }

    DB::transaction(function () use ($product, $nextDue) {
        $updates = [
            'listing_paid_at' => now(),
            'next_due_date'   => $nextDue,
        ];
        if (!empty($product->featured_image)) {
            $updates['is_active'] = true;
        }
        $product->update($updates);
    });

    // Build unique local tx id
    $localTxId = $request->input('transaction_id');
    if (!$localTxId) {
        do {
            $localTxId = 'TRAN_' . time() . Str::upper(Str::random(6));
        } while (Payment::where('local_transaction_id', $localTxId)->exists());
    }

    // If via wallet, record debit with running balance
    if ($data['via'] === 'wallet') {
        $this->appendWalletRow(Auth::id(), 0, $fee, [
            'method'      => 'wallet',
            'description' => "Listing fee ({$planDetails['plan']})",
        ]);

        // Notification: listing fee paid for a specific product
        Activity::create([
            'user_id'      => Auth::id(),
            'is_read'      => false,
            'type'         => Activity::TYPE_PRODUCT,
            'related_id'   => $product->id,
            'related_type' => 'product',
            'description'  => 'You paid for a listing fee of $' . number_format($fee, 2),
            'properties'   => [
                'product_id' => $product->id,
                'plan'       => $planDetails['plan'],
            ],
        ]);
    }

    Payment::create([
        'shop_id'              => $product->shop_id,
        'total_amount'         => $fee,
        'payment_method'       => $data['via'],
        'paymentStatus'        => 3,
        'payment_status'       => 'successful',
        'currency'             => $product->currency ?? 'USD',
        'local_transaction_id' => $localTxId,
        'payment_name'         => 'listing_fee',
    ]);

    return view('products.success_deposit_fee', [
        'product'   => $product,
        'plan'      => $planDetails['plan'],
        'planLabel' => $planDetails['label'],
        'amount'    => $fee,
        'nextDue'   => $nextDue,
    ]);
}
public function payOrder(Request $request, $id)
    {
        // Retrieve the order/invoice
        $order = Order::findOrFail($id);

        if ($order->isPaid()) {
            return redirect()
                ->route('buyer.orders.show', $order->id)
                ->with('error', 'This order has already been paid.');
        }

        // Determine payment method: default to 'wallet' (wallet covers fully, or we top-up then pay)
        $method = strtolower((string) $request->get('method', 'wallet'));
        $allowedMethods = ['wallet', 'paypal', 'mpesa', 'stripe', 'card', 'cash'];
        if (!in_array($method, $allowedMethods, true)) {
            $method = 'wallet';
        }

        // Prepare a unique local transaction ID if not provided
        // (e.g., PayPal flow might not send one; MPESA flow might include its own)
        $localTxId = $request->get('transaction_id');
        if (!$localTxId) {
            do {
                $localTxId = 'TRAN_' . time() . Str::upper(Str::random(6));
            } while (Payment::where('local_transaction_id', $localTxId)->exists());
        }

        // Determine currency sign dynamically
        // (assume order has a currency column; fallback to 'USD')
        $currency = $order->currency ?? 'USD';

        // Build the payment data array
        $paymentData = [
            'order_id'             => $order->id,
            'user_id'              => $order->user_id,
            'shop_id'              => $order->shop_id,
            'total_amount'         => $order->total_amount,
            'payment_method'       => $method,
            'paymentStatus'        => 3,
            'payment_status'       => 'successful',
            'payment_name'         => 'order_payment',
            'currency'             => $currency,
            'local_transaction_id' => $localTxId,
        ];




        // If MPESA, you might want to capture the MPESA metadata (e.g., MpesaReceiptNumber)
        if ($method === 'mpesa' && $request->filled('mpesa_receipt')) {
            $paymentData['mpesa_receipt'] = $request->input('mpesa_receipt');
        }

        // Create the payment record
        $payment = Payment::create($paymentData);

        // Mark order as successful if payment record was created
        if ($payment) {
            $wasPending = ($order->status === \App\Models\Order::STATUS_PENDING);
            if ($wasPending) { $this->decrementInventoryForOrder($order); }
            $order->status = \App\Models\Order::STATUS_PROCESSING;
            $order->save();
        }


        Wallet::create([
            'user_id'    => Auth::id(),
             'order_id'             => $order->id,
            'credit'     => 0,
            'debit'      => $order->total_amount,
            'balance'    => 0, // Optional: recalculate after insert
            'reference'  => strtoupper(uniqid('TXN-')),
            'method'     => 'wallet',
            'description'=> 'Paid via wallet ' . ucfirst($method),
        ]);


        $shop = Shop::find($order->shop_id);



        Wallet::create([
            'user_id'    => $shop->user_id,
            'order_id'             => $order->id,
            'credit'     => $order->total_amount,
            'debit'      => 0,
            'balance'    => 0, // Optional: recalculate after insert
            'reference'  => $localTxId,
            'method'     => $method,
            'description'=> 'Order payment',
            'status'     => 'on_hold',
        ]);

        // Send email notifications for successful payment
        try {
            // Load relationships for email
            $order->load(['items.product', 'shop.user']);
            
            // Get the buyer (order user)
            $buyer = $order->user;
            
            // Get the shop owner
            $shopOwner = $shop->user;
            
            // Send email to shop owner
            \Mail::to($shopOwner->email)->send(new \App\Mail\PaymentSuccessShopOwnerMail(
                $order, 
                $shopOwner, 
                $buyer, 
                $shop,
                $payment
            ));
            
            // Send email to buyer
            \Mail::to($buyer->email)->send(new \App\Mail\PaymentSuccessBuyerMail(
                $order, 
                $buyer, 
                $shop,
                $payment
            ));

            // Create activity record for the seller
            Activity::create([
                'user_id' => $shopOwner->id,
                'is_read' => false,
                'description' => 'You received a payment of $' . number_format($order->total_amount, 2)
            ]);

            // Create activity record for the buyer
            Activity::create([
                'user_id' => $buyer->id,
                'is_read' => false,
                'description' => 'You paid for an order of $' . number_format($order->total_amount, 2)
            ]);
        } catch (\Exception $e) {
            // Log email sending error but don't fail the payment process
            \Log::error('Failed to send payment success emails: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'exception' => $e
            ]);
        }

     
      return redirect()->route('buyer.orders.show', $order->id)
            ->with('success', 'Your payment has been received. Your order is being processed; you will receive a call from our sales team shortly.');
    }

}





