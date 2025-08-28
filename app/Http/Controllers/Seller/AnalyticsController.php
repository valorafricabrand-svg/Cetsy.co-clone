<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductView;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $sellerId = Auth::id();
        $shopId   = shop_id(); // helper that returns seller’s shop_id

        $range = $request->input('range', '6months');

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

        /* 1. KPIs */
        $ordersQ = Order::where('user_id', $sellerId)
                        ->where('status', 'paid');

        if ($start && $end) {
            $ordersQ->whereBetween('created_at', [$start, $end]);
        }

        $kpi = (object) [
            'total_sales'     => $ordersQ->sum('total_amount'),
            'total_orders'    => $ordersQ->count(),
            'avg_order_value' => $ordersQ->avg('total_amount'),
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
                $periodic['revenue'][] = optional($rawPeriod->get($d))->revenue ?? 0;
                $periodic['orders'][]  = optional($rawPeriod->get($d))->orders ?? 0;
            }
        }

        /* 3. Top-selling products */
        $topProducts = Product::where('products.shop_id', $shopId)
            ->join('order_items', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'paid')
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

        $salesSub = Order::where('status', 'paid')
            ->where('user_id', $sellerId)
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

        return view('seller.analytics.index', [
            'kpi'        => $kpi,
            'chart'      => $periodic,
            'topProducts'=> $topProducts,
            'performance'=> $performance,
            'range'      => $range,
            'rangeLabel' => $rangeLabel,
        ]);
    }
}

