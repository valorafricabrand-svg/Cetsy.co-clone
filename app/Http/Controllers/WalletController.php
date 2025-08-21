<?php

namespace App\Http\Controllers;

use App\Helpers\SafaricomDarajaHelper;
use App\Models\Activity;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    /* -------------------------------------------------------------
     | Helpers
     |--------------------------------------------------------------
     */

    /**
     * Get the latest known balance for a user.
     */
    protected function currentBalance(int $userId): float
    {
        // Prefer last row's balance (fast and consistent)
        $last = Wallet::where('user_id', $userId)->latest('id')->value('balance');
        if ($last !== null) return (float) $last;

        // Fallback: sum credits - debits
        return (float) (Wallet::where('user_id', $userId)->selectRaw('COALESCE(SUM(credit - debit),0) as bal')->value('bal') ?? 0);
    }

    /**
     * Append a wallet row with running balance updated atomically.
     */
    protected function appendWalletRow(int $userId, float $credit, float $debit, array $overrides = []): Wallet
    {
        return DB::transaction(function () use ($userId, $credit, $debit, $overrides) {
            // Row-level "for update" pattern via last row read inside txn:
            $prev = $this->currentBalance($userId);
            $newBalance = $prev + $credit - $debit;

            $data = array_merge([
                'user_id'    => $userId,
                'credit'     => $credit,
                'debit'      => $debit,
                'balance'    => $newBalance,
                'reference'  => strtoupper(uniqid('TXN-')),
                'method'     => $overrides['method'] ?? 'wallet',
                'description'=> $overrides['description'] ?? null,
                'external_id'=> $overrides['external_id'] ?? null, // e.g. CheckoutRequestID, PayPal order id
            ], $overrides);

            /** @var Wallet $row */
            $row = Wallet::create($data);
            return $row;
        });
    }

    /* -------------------------------------------------------------
     | Views
     |--------------------------------------------------------------
     */

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
                ->selectRaw('SUM(credit - debit) as balance')
                ->value('balance') ?? 0;

    // Fetch payment methods for the current user's shop
    $shop = Shop::where('user_id', Auth::id())->first();
    $paymentMethods = collect();
    
    if ($shop) {
        $paymentMethods = PaymentMethod::where('shop_id', $shop->id)
            ->with('paymentType')
            ->get();
    }

    return view('wallet.index', compact('transactions', 'balance', 'paymentMethods'));
}

    public function depositForm()
    {
        $balance = $this->currentBalance(auth()->id());
        return view('wallet.deposit', compact('balance'));
    }

    /* -------------------------------------------------------------
     | Manual/Generic deposit (fallback)
     |--------------------------------------------------------------
     */

    public function storeDeposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:mpesa,card,paypal',
        ]);

        $userId = Auth::id();
        $amount = (float) $request->amount;

        $row = $this->appendWalletRow($userId, $amount, 0, [
            'method'      => $request->method,
            'description' => 'Manual deposit via ' . ucfirst($request->method),
        ]);

        Activity::create([
            'user_id'     => $userId,
            'is_read'     => false,
            'description' => 'You made a manual deposit of $' . number_format($amount, 2),
        ]);

        return redirect()->route('wallet.index')->with('success', 'Deposit recorded successfully!');
    }

    /* -------------------------------------------------------------
     | PayPal deposit (AJAX from your blade)
     |--------------------------------------------------------------
     */

    public function handlePayPalDeposit(Request $request)
    {
        $request->validate([
            'amount'   => 'required|numeric|min:1',
            'order_id' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();
        $amount = (float) $request->amount;
        $orderId = $request->input('order_id');

        try {
            // (Optional) Verify PayPal order server-side here.

            $row = $this->appendWalletRow($user->id, $amount, 0, [
                'method'      => 'paypal',
                'description' => 'Deposit via PayPal',
                'external_id' => $orderId,
            ]);

            // email wrapped in try—won’t fail the flow
            try {
                \Mail::to($user->email)->send(new \App\Mail\WalletDepositSuccessMail(
                    $user,
                    $row,
                    $amount,
                    $row->reference
                ));
            } catch (\Throwable $emailEx) {
                Log::error('Wallet PayPal deposit email failed', [
                    'user_id' => $user->id,
                    'wallet_id' => $row->id,
                    'error' => $emailEx->getMessage(),
                ]);
            }

            Activity::create([
                'user_id'     => $user->id,
                'is_read'     => false,
                'description' => 'You made a deposit of $' . number_format($amount, 2),
            ]);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Something went wrong. ' . $e->getMessage(),
            ], 500);
        }
    }

    /* -------------------------------------------------------------
     | M-Pesa C2B (STK Push) deposit
     | Frontend: POST /wallet/deposit/mpesa/stk with {usd_amount, phone}
     | Callback: POST /wallet/deposit/mpesa/callback (no CSRF)
     |--------------------------------------------------------------
     */

    public function startMpesaStk(Request $request)
    {
        $request->validate([
            'usd_amount' => ['required','numeric','min:1'],
            'phone'      => ['required','string','max:20'],
        ]);

        $user   = $request->user();
        $usd    = (float) $request->input('usd_amount');
        $rate   = (float) env('USD_TO_KES', 130);
        $kes    = (int) ceil($usd * $rate);
        $ref    = 'WALLET' . $user->id;
        $desc   = 'Wallet Topup';

    
$phone = ltrim($request->input('phone'), '0+');
    if (!str_starts_with($phone, '254')) {
        $phone = '254' . $phone;
    }



        $resp = SafaricomDarajaHelper::stkPushRequest($phone, $kes, $ref, $desc);

        if (($resp['status'] ?? '') !== 'success') {
            return response()->json([
                'success' => false,
                'message' => $resp['message'] ?? 'Failed to initiate M-Pesa STK.',
            ], 422);
        }

        $data       = $resp['data'] ?? [];
        $checkoutId = $data['CheckoutRequestID'] ?? null;
        $merchantId = $data['MerchantRequestID'] ?? null;

        // Store a PENDING marker row so user sees the attempt in history (optional)
        // Use external_id to dedupe later in callback before crediting again.
        Wallet::firstOrCreate(
            [
                'user_id'    => $user->id,
                'external_id'=> $checkoutId ?: $merchantId,
                'method'     => 'mpesa_stk',
                'credit'     => 0,
                'debit'      => 0,
                'balance'    => $this->currentBalance($user->id), // keep same until callback confirms
            ],
            [
                'reference'   => strtoupper(uniqid('TXN-')),
                'description' => 'M-Pesa STK initiated (KES ' . $kes . ' for $' . number_format($usd, 2) . ')',
                'meta'        => json_encode(['rate' => $rate]),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'STK Push sent. Complete on your phone.',
        ]);
    }

    /**
     * Safaricom callback for STK Push result.
     * Ensure this path is excluded from CSRF in VerifyCsrfToken.
     */
    public function mpesaCallback(Request $request)
    {
        $payload = $request->all();
        Log::info('M-Pesa Callback payload', $payload);

        $cb = data_get($payload, 'Body.stkCallback');
        if (!$cb) {
            Log::warning('M-Pesa Callback: Missing stkCallback body');
            return response()->json(['ok' => true]);
        }

        $checkoutId = data_get($cb, 'CheckoutRequestID');
        $resultCode = (string) data_get($cb, 'ResultCode', '');
        $resultDesc = data_get($cb, 'ResultDesc');

        // Find the pending marker (if created). If not found, we will still try to proceed safely.
        $marker = Wallet::where('method', 'mpesa_stk')
            ->where('external_id', $checkoutId)
            ->orderByDesc('id')
            ->first();

        if ($resultCode !== '0') {
            if ($marker) {
                $marker->description = trim(($marker->description ?? '') . ' | Failed: ' . $resultDesc);
                $marker->save();
            }
            return response()->json(['ok' => true]);
        }

        // Extract paid amount (KES) if present
        $items = data_get($cb, 'CallbackMetadata.Item', []);
        $paidKes = null;
        foreach ($items as $i) {
            if (($i['Name'] ?? '') === 'Amount') {
                $paidKes = (float) ($i['Value'] ?? 0);
            }
        }

        // If we had stored USD/KES intent in marker->meta, use that to compute USD credit:
        $usdCredit = null;
        $rate = (float) env('USD_TO_KES', 130);
        if ($marker && $marker->meta) {
            $meta = is_array($marker->meta) ? $marker->meta : json_decode($marker->meta, true);
            if (isset($meta['rate'])) $rate = (float) $meta['rate'];
        }
        if ($paidKes !== null) {
            $usdCredit = round($paidKes / max($rate, 0.0001), 2);
        }

        // If already credited (idempotency), exit
        $already = Wallet::where('method', 'mpesa_stk')
            ->where('external_id', $checkoutId)
            ->where('credit', '>', 0)
            ->exists();

        if ($already) {
            return response()->json(['ok' => true]);
        }

        // We need a user to credit. If marker exists, use it; otherwise, abort safely.
        if (!$marker) {
            Log::warning('M-Pesa Callback: Marker not found for CheckoutRequestID ' . $checkoutId);
            return response()->json(['ok' => true]);
        }

        // Credit the wallet now
        $userId = $marker->user_id;
        $creditUsd = $usdCredit ?? 0; // if missing, credit 0 (rare) — you can choose to skip crediting in that case

        if ($creditUsd > 0) {
            $this->appendWalletRow($userId, $creditUsd, 0, [
                'method'      => 'mpesa_stk',
                'description' => 'M-Pesa deposit',
                'external_id' => $checkoutId,
            ]);
        }

        // Update marker note
        $marker->description = trim(($marker->description ?? '') . ' | Success (' . ($paidKes !== null ? 'KES ' . $paidKes : 'amount unknown') . ')');
        $marker->save();

        return response()->json(['ok' => true]);
    }

    /* -------------------------------------------------------------
     | Listing payments (wallet / paypal)
     |--------------------------------------------------------------
     */

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

        Wallet::create([
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
        dd('teysysyss');
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
     |--------------------------------------------------------------
     */

    public function payOrder(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if (method_exists($order, 'isPaid') && $order->isPaid()) {
            return redirect()
                ->route('buyer.orders.show', $order->id)
                ->with('error', 'This order has already been paid.');
        }

        $method = $request->get('method', 'wallet');

        // local transaction id
        $localTxId = $request->get('transaction_id');
        if (!$localTxId) {
            do {
                $localTxId = 'TRAN_' . time() . Str::upper(Str::random(6));
            } while (Payment::where('local_transaction_id', $localTxId)->exists());
        }

        $currency = $order->currency ?? 'USD';
        $amount   = (float) $order->total_amount;

        // If paying by wallet, ensure buyer has enough funds BEFORE marking order
        if ($method === 'wallet') {
            $buyerBal = $this->currentBalance(Auth::id());
            if ($buyerBal < $amount) {
                return back()->with('error', 'Insufficient wallet balance.');
            }
        }

        // Create payment record
        $payment = Payment::create([
            'order_id'             => $order->id,
            'user_id'              => $order->user_id,
            'shop_id'              => $order->shop_id,
            'total_amount'         => $amount,
            'payment_method'       => $method,
            'status'               => '3',
            'currency'             => $currency,
            'local_transaction_id' => $localTxId,
            'mpesa_receipt'        => $request->filled('mpesa_receipt') ? $request->input('mpesa_receipt') : null,
        ]);

        // Mark order status (lightweight workflow — adjust to your states)
        $order->status = 'processing';
        $order->save();

        // Wallet movements:
        // 1) Buyer debit (if method == wallet)
        if ($method === 'wallet') {
            $this->appendWalletRow(Auth::id(), 0, $amount, [
                'method'      => 'wallet',
                'description' => 'Order payment',
                'external_id' => $localTxId,
            ]);
        }

        // 2) Seller credit (always credit seller’s wallet record for easy ledger)
        $shop = Shop::find($order->shop_id);
        if ($shop) {
            $this->appendWalletRow($shop->user_id, $amount, 0, [
                'method'      => $method,
                'description' => 'Order payment received',
                'external_id' => $localTxId,
            ]);
        }

        // Emails (best-effort)
        try {
            $order->load(['items.product', 'shop.user']);
            $buyer     = $order->user;
            $shopOwner = $shop?->user;

            if ($shopOwner) {
                \Mail::to($shopOwner->email)->send(new \App\Mail\PaymentSuccessShopOwnerMail(
                    $order, $shopOwner, $buyer, $shop, $payment
                ));
            }

            if ($buyer) {
                \Mail::to($buyer->email)->send(new \App\Mail\PaymentSuccessBuyerMail(
                    $order, $buyer, $shop, $payment
                ));
            }

            if ($shopOwner) {
                Activity::create([
                    'user_id'     => $shopOwner->id,
                    'is_read'     => false,
                    'description' => 'You received a payment of $' . number_format($amount, 2),
                ]);
            }

            if ($buyer) {
                Activity::create([
                    'user_id'     => $buyer->id,
                    'is_read'     => false,
                    'description' => 'You paid for an order of $' . number_format($amount, 2),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Payment success emails failed', [
                'order_id'   => $order->id,
                'payment_id' => $payment->id ?? null,
                'error'      => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('buyer.orders.show', $order->id)
            ->with('success', 'Your payment has been received. Your order is being processed; you will receive a call from our sales team shortly.');
    }
}
