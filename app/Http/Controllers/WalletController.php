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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WalletController extends Controller
{
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

        if ($request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(10);

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

        return view(
            'wallet.index',
            compact('transactions', 'balance', 'onHold', 'paymentMethods', 'paymentTypes', 'feeRate', 'minAmount', 'maxPayout', 'otpPendingPayout')
        );
    }

    public function depositForm()
    {
        $balance = $this->currentBalance(auth()->id());
        return view('wallet.deposit', compact('balance'));
    }

    /* -------------------------------------------------------------
     | Manual/Generic deposit (fallback)
     |-------------------------------------------------------------- */

    public function storeDeposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:mpesa,card,paypal',
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
    $request->validate([
        'amount' => 'required|numeric|min:1',
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

        // 2) Validate plan & via
        $data = $request->validate([
            'plan' => ['required', 'in:monthly,4months'],
            'via'  => ['required', 'in:wallet,paypal'],
        ]);

        // 3) Compute fee & next due date
        $fourMonthFee = (float) $product->category->listing_fee;

        if ($data['plan'] === 'monthly') {
            $fee     = $fourMonthFee / 4;
            $nextDue = now()->addMonth();
        } else {
            $fee     = $fourMonthFee;
            $nextDue = now()->addMonths(4);
        }

        // 4) Activate product & set due date
        $product->update([
            'is_active'       => true,
            'listing_paid_at' => now(),
            'next_due_date'   => $nextDue,
        ]);

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

            $wallet = Wallet::create([
                'user_id'     => Auth::id(),
                'credit'      => 0,
                'debit'       => $fee,
                'balance'     => $currentBalance - $fee,
                'reference'   => strtoupper(uniqid('TXN-')),
                'method'      => 'wallet',
                'description' => "Listing fee ({$data['plan']})",
            ]);

        // Create activity record for the seller
        Activity::create([
            'user_id' => Auth::id(),
            'is_read' => false,
            'description' => 'You paid for a listing fee of $' . number_format($fee, 2)
        ]);
    }

        // 7) Record the Payment
        Payment::create([
            'shop_id'              => $product->shop_id,
            'total_amount'         => $fee,
            'payment_method'       => $data['via'],
            'status'               => '3',  // completed
            'currency'             => $product->currency ?? 'USD',
            'local_transaction_id' => $localTxId,
            'payment_name'         => 'listing_fee',
        ]);

        // 8) Redirect with success
        return view('products.success_deposit_fee', [
            'product' => $product,
            'plan'    => $data['plan'],
            'amount'  => $fee,
            'nextDue' => $nextDue,
        ]);
    }

    public function payListing2(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'plan' => ['required', 'in:monthly,4months'],
            'via'  => ['required', 'in:wallet,paypal'],
        ]);

        $fourMonthFee = (float) $product->category->listing_fee;

        if ($data['plan'] === 'monthly') {
            $fee     = $fourMonthFee / 4;
            $nextDue = now()->addMonth();
        } else {
            $fee     = $fourMonthFee;
            $nextDue = now()->addMonths(4);
        }

        // If paying by wallet, check balance first
        if ($data['via'] === 'wallet') {
            $bal = $this->currentBalance(Auth::id());
            if ($bal < $fee) {
                return back()->with('error', 'Insufficient wallet balance.');
            }
        }

        DB::transaction(function () use ($product, $nextDue) {
            $product->update([
                'is_active'       => true,
                'listing_paid_at' => now(),
                'next_due_date'   => $nextDue,
            ]);
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
                'description' => "Listing fee ({$data['plan']})",
            ]);

            Activity::create([
                'user_id'     => Auth::id(),
                'is_read'     => false,
                'description' => 'You paid for a listing fee of $' . number_format($fee, 2),
            ]);
        }

        Payment::create([
            'shop_id'              => $product->shop_id,
            'total_amount'         => $fee,
            'payment_method'       => $data['via'],
            'status'               => '3',  // completed
            'currency'             => $product->currency ?? 'USD',
            'local_transaction_id' => $localTxId,
            'payment_name'         => 'listing_fee',
        ]);

        return view('products.success_deposit_fee', [
            'product' => $product,
            'plan'    => $data['plan'],
            'amount'  => $fee,
            'nextDue' => $nextDue,
        ]);
    }

    /* -------------------------------------------------------------
     | Order payments (wallet / mpesa / paypal)
     |-------------------------------------------------------------- */

public function payOrder(Request $request, $id)
    {
        // Retrieve the order/invoice
        $order = Order::findOrFail($id);

        if ($order->isPaid()) {
            return redirect()
                ->route('buyer.orders.show', $order->id)
                ->with('error', 'This order has already been paid.');
        }

        // Determine payment method: default to 'paypal'
        $method = $request->get('method', 'wallet');

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
            'status'               => '3',
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
            $order->status = 'processing';
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
            'description'=> 'Paid via wallet ' . ucfirst($request->method),
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
