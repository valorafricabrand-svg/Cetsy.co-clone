<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PayoutRequest;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with business insights.
     */
    public function index()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfPrevMonth = $startOfMonth->copy()->subMonth();
        $endOfPrevMonth = $startOfMonth->copy()->subSecond();
        $startOf30Days = $now->copy()->subDays(29)->startOfDay();

        $excludedStatuses = ['canceled', 'cancelled', 'refunded', 'returned'];

        $totalOrders = Order::count();
        $ordersThisMonth = Order::where('created_at', '>=', $startOfMonth)->count();
        $ordersLastMonth = Order::whereBetween('created_at', [$startOfPrevMonth, $endOfPrevMonth])->count();
        $fulfilledOrders = Order::whereNotIn('status', $excludedStatuses)->count();

        $totalRevenue = Order::whereNotIn('status', $excludedStatuses)->sum('total_amount');
        $revenueToday = Order::whereNotIn('status', $excludedStatuses)
            ->whereDate('created_at', $now->toDateString())
            ->sum('total_amount');
        $revenueThisMonth = Order::whereNotIn('status', $excludedStatuses)
            ->where('created_at', '>=', $startOfMonth)
            ->sum('total_amount');
        $revenueLastMonth = Order::whereNotIn('status', $excludedStatuses)
            ->whereBetween('created_at', [$startOfPrevMonth, $endOfPrevMonth])
            ->sum('total_amount');

        $averageOrderValue = $fulfilledOrders > 0 ? round($totalRevenue / $fulfilledOrders, 2) : 0.0;
        $ordersGrowthPct = $ordersLastMonth > 0
            ? round((($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100, 1)
            : null;
        $revenueGrowthPct = $revenueLastMonth > 0
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
            : null;

        $activeSellers = User::where('user_type', 'seller')->count();
        $newSellersThisMonth = User::where('user_type', 'seller')
            ->where('created_at', '>=', $startOfMonth)
            ->count();
        $activeCustomers = User::where('user_type', 'buyer')->count();
        $newCustomers30 = User::where('user_type', 'buyer')
            ->where('created_at', '>=', $startOf30Days)
            ->count();

        $activeListings = Product::where('is_active', 1)->count();
        $totalListings = Product::count();
        $shopsTotal = Shop::count();

        $monthlyBuckets = collect(range(0, 11))->mapWithKeys(function ($offset) use ($now) {
            $month = $now->copy()->subMonths(11 - $offset)->startOfMonth();
            return [$month->format('Y-m') => [
                'label' => $month->format('M Y'),
                'revenue' => 0.0,
                'orders' => 0,
            ]];
        });

        $firstBucketKey = $monthlyBuckets->keys()->first();
        $firstBucketDate = $firstBucketKey
            ? Carbon::createFromFormat('Y-m', $firstBucketKey)->startOfMonth()
            : $now->copy()->subMonths(11)->startOfMonth();

        $ordersForTrend = Order::where('created_at', '>=', $firstBucketDate)
            ->get(['created_at', 'total_amount', 'status']);

        foreach ($ordersForTrend as $order) {
            $bucketKey = $order->created_at?->copy()->startOfMonth()->format('Y-m');
            if (!$bucketKey || !isset($monthlyBuckets[$bucketKey])) {
                continue;
            }

            $monthlyBuckets[$bucketKey]['orders']++;
            if (!in_array($order->status, $excludedStatuses, true)) {
                $monthlyBuckets[$bucketKey]['revenue'] += (float) $order->total_amount;
            }
        }

        $trendLabels = $monthlyBuckets->pluck('label')->values();
        $trendRevenue = $monthlyBuckets->pluck('revenue')->map(fn ($value) => round($value, 2))->values();
        $trendOrders = $monthlyBuckets->pluck('orders')->values();

        $statusBreakdown = Order::select('status', DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', $startOf30Days)
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->status ?? 'unknown' => (int) $row->total]);

        $payoutStatusBreakdown = PayoutRequest::select('status', DB::raw('SUM(amount) as total'))
            ->where('created_at', '>=', $startOf30Days)
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->status ?? 'unknown' => round((float) $row->total, 2)]);

        $payoutsPendingTotal = PayoutRequest::whereIn('status', ['pending', 'approved', 'sent'])->sum('amount');
        $payoutsPaid30 = PayoutRequest::where('status', 'paid')
            ->where('created_at', '>=', $startOf30Days)
            ->sum('amount');
        $payoutsRequested30 = PayoutRequest::where('created_at', '>=', $startOf30Days)->sum('amount');

        $pendingPayouts = PayoutRequest::with(['user:id,name', 'paymentMethod.paymentType:id,name'])
            ->whereIn('status', ['pending', 'approved', 'sent'])
            ->latest()
            ->limit(5)
            ->get();

        $recentOrders = Order::with(['customer:id,name', 'shop:id,name'])
            ->latest('created_at')
            ->limit(6)
            ->get(['id', 'user_id', 'shop_id', 'total_amount', 'status', 'created_at']);

        $topProductsRaw = OrderItem::select('order_items.product_id')
            ->selectRaw('SUM(order_items.quantity) as units')
            ->selectRaw('SUM(order_items.quantity * order_items.price) as revenue')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.created_at', '>=', $startOf30Days)
            ->whereNotIn('orders.status', $excludedStatuses)
            ->groupBy('order_items.product_id')
            ->orderByDesc(DB::raw('SUM(order_items.quantity * order_items.price)'))
            ->limit(5)
            ->get();

        $topProductModels = Product::whereIn('id', $topProductsRaw->pluck('product_id')->filter())->get(['id', 'name', 'slug']);
        $topProducts = $topProductsRaw->map(function ($row) use ($topProductModels) {
            $product = $topProductModels->firstWhere('id', $row->product_id);
            return [
                'id' => $row->product_id,
                'name' => $product?->name ?? ('Product #' . $row->product_id),
                'slug' => $product?->slug,
                'units' => (int) $row->units,
                'revenue' => round((float) $row->revenue, 2),
            ];
        });

        $topShopsRaw = Order::select('shop_id')
            ->selectRaw('SUM(total_amount) as revenue')
            ->selectRaw('COUNT(*) as orders_count')
            ->where('created_at', '>=', $startOf30Days)
            ->whereNotIn('status', $excludedStatuses)
            ->groupBy('shop_id')
            ->orderByDesc(DB::raw('SUM(total_amount)'))
            ->limit(5)
            ->get();

        $shopModels = Shop::whereIn('id', $topShopsRaw->pluck('shop_id')->filter())->get(['id', 'name']);
        $topShops = $topShopsRaw->map(function ($row) use ($shopModels) {
            $shop = $shopModels->firstWhere('id', $row->shop_id);
            return [
                'id' => $row->shop_id,
                'name' => $shop?->name ?? ('Shop #' . $row->shop_id),
                'orders' => (int) $row->orders_count,
                'revenue' => round((float) $row->revenue, 2),
            ];
        });

        return view('admin.dashboard', [
            'metrics' => [
                'totalRevenue' => round((float) $totalRevenue, 2),
                'revenueToday' => round((float) $revenueToday, 2),
                'revenueThisMonth' => round((float) $revenueThisMonth, 2),
                'revenueGrowthPct' => $revenueGrowthPct,
                'ordersTotal' => $totalOrders,
                'ordersThisMonth' => $ordersThisMonth,
                'ordersGrowthPct' => $ordersGrowthPct,
                'averageOrderValue' => $averageOrderValue,
                'fulfilledOrders' => $fulfilledOrders,
                'activeSellers' => $activeSellers,
                'newSellersThisMonth' => $newSellersThisMonth,
                'activeCustomers' => $activeCustomers,
                'newCustomers30' => $newCustomers30,
                'activeListings' => $activeListings,
                'totalListings' => $totalListings,
                'shopsTotal' => $shopsTotal,
                'payoutsPendingTotal' => round((float) $payoutsPendingTotal, 2),
                'payoutsPaid30' => round((float) $payoutsPaid30, 2),
                'payoutsRequested30' => round((float) $payoutsRequested30, 2),
            ],
            'trendLabels' => $trendLabels,
            'trendRevenue' => $trendRevenue,
            'trendOrders' => $trendOrders,
            'statusBreakdown' => $statusBreakdown,
            'payoutStatusBreakdown' => $payoutStatusBreakdown,
            'pendingPayouts' => $pendingPayouts,
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
            'topShops' => $topShops,
        ]);
    }
}
