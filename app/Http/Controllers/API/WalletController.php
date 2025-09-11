<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\PayoutRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    /**
     * Return wallet summary for the authenticated user.
     */
    public function summary(Request $request)
    {
        $user = $request->user();
        if (! $user) return response()->json(['message' => 'Unauthorized'], 401);

        $balance = (float) (Wallet::where('user_id', $user->id)
            ->where('status', 'completed')
            ->selectRaw('COALESCE(SUM(credit - debit),0) as balance')
            ->value('balance') ?? 0);

        $onHold = (float) (Wallet::where('user_id', $user->id)
            ->where('status', 'on_hold')
            ->selectRaw('COALESCE(SUM(credit - debit),0) as balance')
            ->value('balance') ?? 0);

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

        $balance = (float) (Wallet::where('user_id', $user->id)
            ->where('status', 'completed')
            ->selectRaw('COALESCE(SUM(credit - debit),0) as balance')
            ->value('balance') ?? 0);

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

        $payout = PayoutRequest::create([
            'user_id' => $user->id,
            'amount'  => (float) $data['amount'],
            'status'  => 'pending',
            'payment_method_id' => $data['payment_method_id'] ?? null,
            'meta'    => [
                'fee_rate' => $feeRate,
            ],
        ]);

        return response()->json(['message' => 'Payout requested', 'payout' => $payout], 201);
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

        // Prefer settings() helper if available, otherwise env fallback
        $clientId = function_exists('setting') ? (setting('paypal_client_id') ?? '') : (env('PAYPAL_CLIENT_ID', ''));
        return response()->json(['client_id' => $clientId]);
    }
}
