<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductView;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $shopId = shop_id(); // helper that returns seller’s shop_id

        $paidStatuses = [
            Order::STATUS_PROCESSING,
            Order::STATUS_SHIPPED,
            Order::STATUS_DELIVERED,
            Order::STATUS_COMPLETED,
        ];

        $range = $request->input('range', '6months');

        // Determine date range
        if ($range === 'custom') {
            try {
                $start = $request->filled('start')
                    ? Carbon::parse($request->input('start'))->startOfDay()
                    : null;
                $end = $request->filled('end')
                    ? Carbon::parse($request->input('end'))->endOfDay()
                    : null;
            } catch (\Exception $e) {
                $start = $end = null;
            }
        } else {
            [$start, $end] = match ($range) {
                'today'     => [now()->startOfDay(), now()->endOfDay()],
                'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
                'week'      => [now()->subWeek()->startOfDay(), now()],
                '2weeks'    => [now()->subWeeks(2)->startOfDay(), now()],
                '1month'    => [now()->subMonth()->startOfDay(), now()],
                '2months'   => [now()->subMonths(2)->startOfDay(), now()],
                '3months'   => [now()->subMonths(3)->startOfDay(), now()],
                '6months'   => [now()->subMonths(6)->startOfDay(), now()],
                default     => [null, null],
            };
        }

        // Previous period (same length just before current)
        $prevStart = $prevEnd = null;
        if ($start && $end) {
            $days = $start->diffInDays($end) + 1; // inclusive length
            $prevEnd = $start->copy()->subDay();
            $prevStart = $prevEnd->copy()->subDays($days - 1)->startOfDay();
        }

        /* 1. KPIs */
        $ordersQ = Order::where('shop_id', $shopId)
                        ->whereIn('status', $paidStatuses);

        if ($start && $end) {
            $ordersQ->whereBetween('created_at', [$start, $end]);
        }

        $kpi = (object) [
            'total_sales'     => $ordersQ->sum('total_amount'),
            'total_orders'    => $ordersQ->count(),
            'avg_order_value' => $ordersQ->avg('total_amount'),
        ];

        // Previous KPIs and deltas for badges
        $kpiPrev = (object) [
            'total_sales'     => 0.0,
            'total_orders'    => 0,
            'avg_order_value' => 0.0,
        ];
        if ($prevStart && $prevEnd) {
            $ordersPrev = Order::where('shop_id', $shopId)
                ->whereIn('status', $paidStatuses)
                ->whereBetween('created_at', [$prevStart, $prevEnd]);
            $kpiPrev->total_sales     = (float) $ordersPrev->sum('total_amount');
            $kpiPrev->total_orders    = (int) $ordersPrev->count();
            $kpiPrev->avg_order_value = (float) $ordersPrev->avg('total_amount');
        }
        $pctDelta = function ($curr, $prev) {
            $prev = (float) $prev; $curr = (float) $curr;
            if ($prev == 0.0) return $curr > 0 ? 100.0 : 0.0;
            return (($curr - $prev) / $prev) * 100.0;
        };
        $kpiDelta = (object) [
            'sales'  => $pctDelta($kpi->total_sales, $kpiPrev->total_sales),
            'orders' => $pctDelta($kpi->total_orders, $kpiPrev->total_orders),
            'aov'    => $pctDelta($kpi->avg_order_value, $kpiPrev->avg_order_value),
        ];

        /* 2. Revenue & orders for chart */
        $rawPeriod = $ordersQ->clone()
            ->selectRaw('DATE(created_at) as d, SUM(total_amount) as revenue, COUNT(*) as orders')
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy('d');

        $periodic = [
            'labels'  => [],
            'revenue' => [],
            'orders'  => [],
        ];

        if ($start && $end) {
            foreach (CarbonPeriod::create($start, $end) as $date) {
                $d = $date->format('Y-m-d');
                $periodic['labels'][]  = $d;
                $periodic['revenue'][] = (float) (optional($rawPeriod->get($d))->revenue ?? 0);
                $periodic['orders'][]  = (int) (optional($rawPeriod->get($d))->orders ?? 0);
            }
        }

        // Best revenue day within period
        $bestDay = null; $bestRevenue = 0.0;
        foreach ($rawPeriod as $row) {
            $rev = (float) ($row->revenue ?? 0);
            if ($rev > $bestRevenue) { $bestRevenue = $rev; $bestDay = $row->d; }
        }

        // Average daily revenue over selected range
        $avgDailyRevenue = 0.0;
        if ($start && $end) {
            $daysCount = $start->diffInDays($end) + 1;
            if ($daysCount > 0) $avgDailyRevenue = (float) $kpi->total_sales / $daysCount;
        }

        // Sales by Day of Week (Mon..Sun)
        $dowLabels = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
        $dowSeries = [0,0,0,0,0,0,0];
        $dowRaw = $ordersQ->clone()
            ->selectRaw('DAYOFWEEK(created_at) as dow, SUM(total_amount) as revenue')
            ->groupBy('dow')
            ->orderBy('dow')
            ->get();
        $tmp = array_fill(1, 7, 0.0); // 1..7 Sun..Sat
        foreach ($dowRaw as $r) {
            $idx = (int) ($r->dow ?? 0);
            if ($idx >= 1 && $idx <= 7) $tmp[$idx] = (float) $r->revenue;
        }
        // map to Mon..Sun order
        $dowSeries = [
            $tmp[2] ?? 0.0,
            $tmp[3] ?? 0.0,
            $tmp[4] ?? 0.0,
            $tmp[5] ?? 0.0,
            $tmp[6] ?? 0.0,
            $tmp[7] ?? 0.0,
            $tmp[1] ?? 0.0,
        ];

        /* 3. Top-selling products */
        $topProducts = Product::where('products.shop_id', $shopId)
            ->join('order_items', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.status', $paidStatuses)
            ->when($start && $end, fn($q) => $q->whereBetween('orders.created_at', [$start, $end]))
            ->groupBy(
                'products.id',
                'products.name',
                'products.slug',
                'products.shop_id',
                'products.price',
                'products.created_at',
                'products.updated_at'
            )
            ->select(
                'products.id',
                'products.name',
                'products.slug',
                'products.shop_id',
                'products.price',
                'products.created_at',
                'products.updated_at',
                DB::raw('SUM(order_items.quantity) AS qty_sold'),
                DB::raw('SUM(order_items.quantity * order_items.price) AS revenue')
            )
            ->orderByDesc('revenue')
            ->take(5)
            ->get();

        /* 4. Listing performance (views ➜ sales) */
        $viewsSub = ProductView::whereHas('product', fn($q) => $q->where('products.shop_id', $shopId))
            ->when($start && $end, fn($q) => $q->whereBetween('product_views.created_at', [$start, $end]))
            ->selectRaw('product_id, COUNT(*) AS views')
            ->groupBy('product_id');

        $salesSub = Order::whereIn('status', $paidStatuses)
            ->where('shop_id', $shopId)
            ->when($start && $end, fn($q) => $q->whereBetween('orders.created_at', [$start, $end]))
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->selectRaw('order_items.product_id, SUM(order_items.quantity) AS sales')
            ->groupBy('order_items.product_id');

        $performance = Product::where('products.shop_id', $shopId)
            ->leftJoinSub($viewsSub, 'v', 'v.product_id', '=', 'products.id')
            ->leftJoinSub($salesSub, 's', 's.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                'products.slug',
                'products.shop_id',
                'products.price',
                'products.created_at',
                'products.updated_at',
                'v.views',
                's.sales',
                DB::raw('ROUND(IFNULL(s.sales,0) / NULLIF(v.views,0) * 100, 2) AS conversion')
            )
            ->orderByDesc('conversion')
            ->take(10)
            ->get();

        // Overall conversion (units sold / views) across range
        // Use direct aggregates; summing aliases on grouped subqueries would reference non-existent columns.
        $totalViews = (int) ProductView::whereHas('product', fn($q) => $q->where('products.shop_id', $shopId))
            ->when($start && $end, fn($q) => $q->whereBetween('product_views.created_at', [$start, $end]))
            ->count();
        $totalUnits = (int) Order::whereIn('status', $paidStatuses)
            ->where('shop_id', $shopId)
            ->when($start && $end, fn($q) => $q->whereBetween('orders.created_at', [$start, $end]))
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->sum('order_items.quantity');
        $overallConversion = $totalViews > 0 ? round(($totalUnits / $totalViews) * 100, 2) : 0.0;

        if ($range === 'custom' && $start && $end) {
            $rangeLabel = $start->format('M j, Y') . ' - ' . $end->format('M j, Y');
        } else {
            $rangeLabel = [
                'today'     => 'Today',
                'yesterday' => 'Yesterday',
                'week'      => 'Last 7 Days',
                '2weeks'    => 'Last 14 Days',
                '1month'    => 'Last 1 Month',
                '2months'   => 'Last 2 Months',
                '3months'   => 'Last 3 Months',
                '6months'   => 'Last 6 Months',
                'all'       => 'All Time',
            ][$range] ?? 'All Time';
        }

        return view('seller.analytics.index', [
            'kpi'              => $kpi,
            'kpiPrev'          => $kpiPrev,
            'kpiDelta'         => $kpiDelta,
            'chart'            => $periodic,
            'bestDay'          => $bestDay,
            'bestRevenue'      => $bestRevenue,
            'avgDailyRevenue'  => $avgDailyRevenue,
            'dowLabels'        => $dowLabels,
            'dowSeries'        => $dowSeries,
            'topProducts'      => $topProducts,
            'performance'      => $performance,
            'overallConversion'=> $overallConversion,
            'range'            => $range,
            'rangeLabel'       => $rangeLabel,
            'startDate'        => $start ? $start->toDateString() : null,
            'endDate'          => $end ? $end->toDateString() : null,
        ]);
    }
}
