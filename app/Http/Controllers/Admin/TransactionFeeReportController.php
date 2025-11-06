<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionFeeReportController extends Controller
{
    public function index(Request $request)
    {
        $from   = $request->query('from');
        $to     = $request->query('to');
        $userId = $request->query('user_id');
        $order  = $request->query('order_id');
        $ref    = $request->query('reference');

        $query = Wallet::query()
            ->where(function ($q) {
                $q->where('type', 'transaction_fee')
                  ->orWhere('method', 'platform_fee');
            })
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to,   fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->when($userId, fn ($q) => $q->where('user_id', (int) $userId))
            ->when($order, function ($q) use ($order) {
                $q->where('meta->order_id', (int) $order);
            })
            ->when($ref, fn ($q) => $q->where('reference', 'like', "%{$ref}%"))
            ->latest('id');

        $totalFees = (clone $query)->sum(DB::raw('debit - credit'));
        $fees = $query->with('user:id,name,email')->paginate(25)->withQueryString();

        $users = User::orderBy('name')->get(['id','name']);

        return view('admin.reports.transaction_fees', compact('fees','totalFees','from','to','userId','order','ref','users'));
    }

    public function export(Request $request)
    {
        $from   = $request->query('from');
        $to     = $request->query('to');
        $userId = $request->query('user_id');
        $order  = $request->query('order_id');
        $ref    = $request->query('reference');

        $rows = Wallet::query()
            ->where(function ($q) {
                $q->where('type', 'transaction_fee')
                  ->orWhere('method', 'platform_fee');
            })
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to,   fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->when($userId, fn ($q) => $q->where('user_id', (int) $userId))
            ->when($order, function ($q) use ($order) { $q->where('meta->order_id', (int) $order); })
            ->when($ref, fn ($q) => $q->where('reference', 'like', "%{$ref}%"))
            ->with('user:id,name,email')
            ->orderBy('id','desc')
            ->get(['id','user_id','credit','debit','reference','description','created_at','meta']);

        $filename = 'transaction_fees_'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID','User','Email','Debit','Credit','Order ID','Reference','Description','Created At']);
            foreach ($rows as $r) {
                $metaOrder = data_get($r->meta, 'order_id');
                fputcsv($out, [
                    $r->id,
                    optional($r->user)->name,
                    optional($r->user)->email,
                    number_format((float)$r->debit,2),
                    number_format((float)$r->credit,2),
                    $metaOrder,
                    $r->reference,
                    $r->description,
                    $r->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}

