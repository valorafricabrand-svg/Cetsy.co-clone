<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class SubscriptionController extends Controller
{
    public function deactivateExpired(): RedirectResponse
    {
        $count = Subscription::where('status', 'active')
            ->where('end_date', '<', now())
            ->update(['status' => 'inactive']);

        Activity::create([
            'user_id' => Auth::id(),
            'is_read' => false,
            'description' => 'You deactivated expired subscriptions',
            'type' => \App\Models\Activity::TYPE_SUBSCRIPTION,
            'related_id' => $count,
            'related_type' => 'subscription',
        ]);

        return Redirect::back()->with('success', "Deactivated $count expired subscriptions.");
    }

    /**
     * MRR report with optional ?from=YYYY-MM&to=YYYY-MM.
     */
    public function mrr(Request $request): View
    {
        $now = Carbon::now();

        // Parse optional YYYY-MM range
        $toYm = $request->query('to');
        $fromYm = $request->query('from');
        try { $toMonth = $toYm ? Carbon::createFromFormat('Y-m', $toYm)->startOfMonth() : $now->copy()->startOfMonth(); }
        catch (\Throwable $e) { $toMonth = $now->copy()->startOfMonth(); }
        try { $fromMonth = $fromYm ? Carbon::createFromFormat('Y-m', $fromYm)->startOfMonth() : $toMonth->copy()->subMonths(11); }
        catch (\Throwable $e) { $fromMonth = $toMonth->copy()->subMonths(11); }

        if ($fromMonth->greaterThan($toMonth)) { [$fromMonth, $toMonth] = [$toMonth->copy(), $fromMonth->copy()]; }
        if ($fromMonth->diffInMonths($toMonth) > 35) { $fromMonth = $toMonth->copy()->subMonths(35); }

        $isActiveInMonth = function ($q, Carbon $monthStart, Carbon $monthEnd) {
            $q->where('start_date', '<=', $monthEnd)
              ->where(function ($qq) use ($monthStart) {
                  $qq->whereNull('end_date')
                     ->orWhere('end_date', '>=', $monthStart);
              })
              ->where('status', 'active');
        };

        // MRR at end of selected range
        $currentMrr = (float) Subscription::query()
            ->where(fn($q) => $isActiveInMonth($q, $toMonth->copy()->startOfMonth(), $toMonth->copy()->endOfMonth()))
            ->sum('amount');

        // Build months between from..to
        $months = [];
        $cursor = $fromMonth->copy();
        while ($cursor->lte($toMonth)) {
            $ms = $cursor->copy();
            $me = $ms->copy()->endOfMonth();
            $sum = (float) Subscription::query()
                ->where(fn($q) => $isActiveInMonth($q, $ms, $me))
                ->sum('amount');
            $months[] = ['label' => $ms->format('M Y'), 'amount' => $sum, 'ym' => $ms->format('Y-m')];
            $cursor->addMonth();
        }

        return view('admin.reports.mrr', [
            'currentMrr' => $currentMrr,
            'months'     => $months,
            'fromYm'     => $fromMonth->format('Y-m'),
            'toYm'       => $toMonth->format('Y-m'),
        ]);
    }

    /**
     * List shops with active subscriptions for a given month (Y-m) or 'current'.
     */
    public function mrrShops(string $ym): View
    {
        $now = Carbon::now();
        if ($ym === 'current') { $ms = $now->copy()->startOfMonth(); }
        else {
            try { $ms = Carbon::createFromFormat('Y-m', $ym)->startOfMonth(); }
            catch (\Throwable $e) { $ms = $now->copy()->startOfMonth(); }
        }
        $me = $ms->copy()->endOfMonth();

        $subs = Subscription::with(['shop','user'])
            ->where('status', 'active')
            ->where('start_date', '<=', $me)
            ->where(function ($q) use ($ms) { $q->whereNull('end_date')->orWhere('end_date', '>=', $ms); })
            ->orderByDesc('amount')
            ->get();

        $total = (float) $subs->sum('amount');
        $label = $ms->format('F Y');
        return view('admin.reports.mrr_shops', compact('subs', 'total', 'label', 'ms'));
    }

    /**
     * Listing Fee Revenue summary (by month), optional from/to like MRR.
     */
    public function listingFees(Request $request): View
    {
        $now = Carbon::now();

        $toYm = $request->query('to');
        $fromYm = $request->query('from');
        try { $toMonth = $toYm ? Carbon::createFromFormat('Y-m', $toYm)->startOfMonth() : $now->copy()->startOfMonth(); }
        catch (\Throwable $e) { $toMonth = $now->copy()->startOfMonth(); }
        try { $fromMonth = $fromYm ? Carbon::createFromFormat('Y-m', $fromYm)->startOfMonth() : $toMonth->copy()->subMonths(11); }
        catch (\Throwable $e) { $fromMonth = $toMonth->copy()->subMonths(11); }

        if ($fromMonth->greaterThan($toMonth)) { [$fromMonth, $toMonth] = [$toMonth->copy(), $fromMonth->copy()]; }
        if ($fromMonth->diffInMonths($toMonth) > 35) { $fromMonth = $toMonth->copy()->subMonths(35); }

        $sumMonth = function (Carbon $ms, Carbon $me): float {
            return (float) Payment::query()
                ->where('payment_name', 'listing_fee')
                ->where(function ($q) {
                    $q->where('paymentStatus', 3)
                      ->orWhere('payment_status', 'successful');
                })
                ->whereBetween('created_at', [$ms, $me])
                ->sum('total_amount');
        };

        $currentTotal = $sumMonth($toMonth->copy()->startOfMonth(), $toMonth->copy()->endOfMonth());

        $months = [];
        $cursor = $fromMonth->copy();
        while ($cursor->lte($toMonth)) {
            $ms = $cursor->copy();
            $me = $ms->copy()->endOfMonth();
            $months[] = [
                'label' => $ms->format('M Y'),
                'amount' => $sumMonth($ms, $me),
                'ym' => $ms->format('Y-m'),
            ];
            $cursor->addMonth();
        }

        return view('admin.reports.listing_fees', [
            'currentTotal' => $currentTotal,
            'months'       => $months,
            'fromYm'       => $fromMonth->format('Y-m'),
            'toYm'         => $toMonth->format('Y-m'),
        ]);
    }

    /**
     * Drill-down: Listing fee payments for a given month (Y-m or 'current').
     */
    public function listingFeesPayments(Request $request, string $ym): View
    {
        $now = Carbon::now();
        if ($ym === 'current') { $ms = $now->copy()->startOfMonth(); }
        else {
            try { $ms = Carbon::createFromFormat('Y-m', $ym)->startOfMonth(); }
            catch (\Throwable $e) { $ms = $now->copy()->startOfMonth(); }
        }
        $me = $ms->copy()->endOfMonth();

        $filters = [
            'payment_method' => (string) $request->query('payment_method', ''),
        ];

        $query = Payment::with('shop.user')
            ->where('payment_name', 'listing_fee')
            ->where(function ($q) {
                $q->where('paymentStatus', 3)
                  ->orWhere('payment_status', 'successful');
            })
            ->whereBetween('created_at', [$ms, $me])
            ->orderByDesc('total_amount');

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        $payments = $query->get();
        $total    = (float) $payments->sum('total_amount');
        $label    = $ms->format('F Y');

        // Distinct payment methods for filter UI
        $paymentMethods = Payment::query()
            ->where('payment_name', 'listing_fee')
            ->distinct()
            ->pluck('payment_method')
            ->filter()
            ->values();

        return view('admin.reports.listing_fees_payments', [
            'payments'       => $payments,
            'total'          => $total,
            'label'          => $label,
            'ms'             => $ms,
            'filters'        => $filters,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * CSV export for listing fee payments in a given month.
     */
    public function listingFeesExport(Request $request, string $ym)
    {
        $now = Carbon::now();
        if ($ym === 'current') { $ms = $now->copy()->startOfMonth(); }
        else {
            try { $ms = Carbon::createFromFormat('Y-m', $ym)->startOfMonth(); }
            catch (\Throwable $e) { $ms = $now->copy()->startOfMonth(); }
        }
        $me = $ms->copy()->endOfMonth();

        $filters = [
            'payment_method' => (string) $request->query('payment_method', ''),
        ];

        $query = Payment::with('shop.user')
            ->where('payment_name', 'listing_fee')
            ->where(function ($q) {
                $q->where('paymentStatus', 3)
                  ->orWhere('payment_status', 'successful');
            })
            ->whereBetween('created_at', [$ms, $me])
            ->orderByDesc('created_at');
        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }
        $rows = $query->get();

        $filename = 'listing_fees_' . $ms->format('Y_m') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Shop', 'Owner', 'Amount', 'Method', 'Date']);
            foreach ($rows as $p) {
                fputcsv($out, [
                    optional($p->shop)->name,
                    optional(optional($p->shop)->user)->name,
                    (string) $p->total_amount,
                    (string) $p->payment_method,
                    optional($p->created_at)->format('Y-m-d'),
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
