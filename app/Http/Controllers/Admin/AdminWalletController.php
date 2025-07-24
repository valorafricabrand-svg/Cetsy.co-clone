<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminWalletController extends Controller
{
   

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
}
