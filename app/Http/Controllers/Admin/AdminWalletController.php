<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WalletDepositSuccessMail;
use App\Models\Activity;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminWalletController extends Controller
{
    /**
     * Compute current running balance for a user from the latest row,
     * falling back to a SUM if needed.
     */
    private function currentBalance(int $userId): float
    {
        $last = Wallet::where('user_id', $userId)->latest('id')->value('balance');
        if ($last !== null) return (float) $last;
        return (float) (Wallet::where('user_id', $userId)
            ->selectRaw('COALESCE(SUM(credit - debit),0) as bal')
            ->value('bal') ?? 0);
    }

    /**
     * Append a wallet row for the given user with running balance update.
     */
    private function appendWalletRow(int $userId, float $credit, float $debit, array $overrides = []): Wallet
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
                'external_id' => $overrides['external_id'] ?? null,
                'meta'        => $overrides['meta'] ?? null,
            ], $overrides);

            return Wallet::create($data);
        });
    }
   

    /**
     * Display a listing of wallet transactions.
     */
public function index(Request $request)
{
    $perPage = (int) $request->input('per_page', 25);
    $search  = $request->input('q');
    $userId  = $request->input('user_id');
    $type    = $request->input('type');
    $sort    = $request->input('sort', 'created_at');
    $dir     = $request->input('dir', 'desc') === 'asc' ? 'asc' : 'desc';

    $allowedSorts = ['id','credit','debit','balance','created_at'];
    if (!in_array($sort, $allowedSorts, true)) $sort = 'created_at';

    // 1) Build once
    $base = Wallet::query()
        ->with('user:id,name,email')
        ->when($search, function ($q) use ($search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('reference', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        })
        ->when($userId, fn($q) => $q->where('user_id', $userId))
        ->when($type,   fn($q) => $q->where('type', $type));

    // 2) Totals on cloned builder (before paginate)
    $totals = (clone $base)
        ->selectRaw('SUM(credit) AS credit, SUM(debit) AS debit, SUM(balance) AS balance')
        ->first()
        ->toArray();

    // 3) Paginate sorted results
    $wallets = (clone $base)
        ->orderBy($sort, $dir)
        ->paginate($perPage)
        ->appends($request->query());

    $types = Wallet::query()->distinct()->pluck('type')->filter()->values()->all();

    return view('admin.wallets.index', compact(
        'wallets','totals','types','search','userId','type','perPage','sort','dir'
    ));
}


    /**
     * Show one record.
     */
    public function show(Wallet $wallet)
    {
        // $this->authorize('view', $wallet);
        return view('admin.wallets.show', compact('wallet'));
    }

    /**
     * Edit form.
     */
    public function edit(Wallet $wallet)
    {
        // $this->authorize('update', $wallet);
        return view('admin.wallets.edit', compact('wallet'));
    }

    /**
     * Update a record (credit/debit/type/ref/desc).
     */
    public function update(Request $request, Wallet $wallet)
    {
        // $this->authorize('update', $wallet);

        $data = $request->validate([
            'credit'      => 'required|numeric',
            'debit'       => 'required|numeric',
            'balance'     => 'required|numeric',
            'type'        => 'nullable|string|max:255',
            'reference'   => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $wallet->update($data);

        return redirect()->route('admin.wallets.index')->with('success', 'Wallet updated.');
    }

    /**
     * Delete a single row.
     */
    public function destroy(Wallet $wallet)
    {
        // $this->authorize('delete', $wallet);
        $wallet->delete();

        return back()->with('success', 'Wallet transaction deleted.');
    }

    /**
     * Bulk action (delete).
     */
    public function bulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
        ]);

        // $this->authorize('bulk', Wallet::class);
        $ids = $request->input('ids');

        DB::transaction(function () use ($ids) {
            Wallet::whereIn('id', $ids)->delete();
        });

        return back()->with('success', 'Selected wallet rows deleted: '.count($ids));
    }

    /**
     * Show admin top-up form.
     */
    public function create(Request $request)
    {
        $prefill = [
            'user'        => $request->query('user'),
            'user_id'     => $request->query('user_id'),
            'email'       => $request->query('email'),
            'amount'      => $request->query('amount'),
            'description' => $request->query('description'),
        ];
        return view('admin.wallets.create', compact('prefill'));
    }

    /**
     * Persist an admin wallet top-up to a seller's wallet.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'seller'       => 'required|string|max:191', // ID or email
            'amount'       => 'required|numeric|min:0.01',
            'description'  => 'nullable|string|max:1000',
        ]);

        // Resolve seller by ID or email
        $seller = null;
        $raw = trim($data['seller']);
        if (ctype_digit($raw)) {
            $seller = User::find((int) $raw);
        }
        if (!$seller && filter_var($raw, FILTER_VALIDATE_EMAIL)) {
            $seller = User::where('email', $raw)->first();
        }
        if (!$seller) {
            return back()->withInput()->with('error', 'Seller not found. Enter a valid seller ID or email.');
        }
        if (!method_exists($seller, 'isSeller') || !$seller->isSeller()) {
            return back()->withInput()->with('error', 'Selected user is not a seller.');
        }

        // Create credit row with running balance
        $amount = (float) $data['amount'];
        $desc = $data['description'] ?: 'Admin top-up';

        $row = $this->appendWalletRow($seller->id, $amount, 0.0, [
            'method'      => 'admin_topup',
            'description' => $desc,
            'status'      => 'completed',
        ]);

        // Activity + email (best-effort)
        try {
            Activity::create([
                'user_id'     => $seller->id,
                'is_read'     => false,
                'description' => 'Your wallet was topped up by admin: $' . number_format($amount, 2),
                'type'        => Activity::TYPE_WALLET,
            ]);
        } catch (\Throwable $e) {
            Log::warning('admin.wallet.topup.activity_failed', ['seller_id' => $seller->id, 'error' => $e->getMessage()]);
        }
        try {
            if ($seller->email) {
                Mail::to($seller->email)->send(new WalletDepositSuccessMail($seller, $row, $amount, $row->reference));
            }
        } catch (\Throwable $e) {
            Log::warning('admin.wallet.topup.mail_failed', ['seller_id' => $seller->id, 'error' => $e->getMessage()]);
        }

        return redirect()->route('admin.wallets.index')
            ->with('success', 'Wallet topped up successfully for seller #' . $seller->id . '.');
    }
}
