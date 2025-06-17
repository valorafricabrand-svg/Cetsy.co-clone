<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductView;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        $sellerId = Auth::id();
        $shopId   = shop_id(); // helper that returns seller’s shop_id

        /* 1. KPIs */
        $ordersQ = Order::where('user_id', $sellerId)
                        ->where('status', 'paid');

        $kpi = (object) [
            'total_sales'     => $ordersQ->sum('total_amount'),
            'total_orders'    => $ordersQ->count(),
            'avg_order_value' => $ordersQ->avg('total_amount'),
        ];

        /* 2. Monthly revenue (12 mo) */
        $rawMonthly = $ordersQ->clone()
            ->selectRaw('DATE_FORMAT(created_at,"%Y-%m") as ym, SUM(total_amount) as revenue')
            ->groupBy('ym')
            ->pluck('revenue', 'ym')
            ->toArray();

        $monthly = [];
        foreach (CarbonPeriod::create(now()->subMonths(11)->startOfMonth(), '1 month', now()) as $date) {
            $ym = $date->format('Y-m');
            $monthly[$ym] = $rawMonthly[$ym] ?? 0;
        }

        /* 3. Top-selling products */
        $topProducts = Product::where('products.shop_id', $shopId)
            ->join('order_items', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'paid')
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
            ->selectRaw('product_id, COUNT(*) AS views')
            ->groupBy('product_id');

        $salesSub = Order::where('status', 'paid')
            ->where('user_id', $sellerId)
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

        return view('seller.analytics.index', compact('kpi', 'monthly', 'topProducts', 'performance'));
    }
}
