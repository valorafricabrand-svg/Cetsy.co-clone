<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:32'],
            'shop_id' => ['nullable', 'integer', 'exists:shops,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $query = Order::query();
        $this->applyFilters($query, $validated);

        $orders = (clone $query)
            ->with([
                'customer:id,name,email',
                'shop:id,name',
            ])
            ->withCount(['items', 'disputes'])
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        $filteredCount = (clone $query)->count();
        $filteredGrossAmount = (float) (clone $query)->sum('total_amount');
        $pendingCount = (clone $query)->where('status', Order::STATUS_PENDING)->count();
        $inTransitCount = (clone $query)
            ->whereIn('status', [Order::STATUS_PROCESSING, Order::STATUS_SHIPPED])
            ->count();

        $statusCounts = Order::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $shops = Shop::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        return view('admin.orders.index', [
            'orders' => $orders,
            'shops' => $shops,
            'statusOptions' => $this->statusOptions($statusCounts),
            'statusCounts' => $statusCounts,
            'summary' => [
                'filteredCount' => $filteredCount,
                'filteredGrossAmount' => $filteredGrossAmount,
                'pendingCount' => $pendingCount,
                'inTransitCount' => $inTransitCount,
            ],
        ]);
    }

    public function show(Order $order)
    {
        $order->load([
            'customer:id,name,email,phone',
            'shop:id,name,user_id',
            'shop.user:id,name,email,phone',
            'payment',
            'items.product:id,name,slug',
            'items.variation',
            'items.shippingProfile',
            'disputes:id,order_id,status,created_at',
        ]);

        return view('admin.orders.show', compact('order'));
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));
        $shopId = $filters['shop_id'] ?? null;
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;

        if ($search !== '') {
            $numericSearch = preg_replace('/\D+/', '', $search) ?: null;

            $query->where(function (Builder $builder) use ($search, $numericSearch) {
                if (!empty($numericSearch)) {
                    $builder->orWhere('id', (int) $numericSearch);
                }

                $builder
                    ->orWhere('full_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('tracking_no', 'like', '%' . $search . '%');
            });
        }

        if ($status !== '' && $status !== 'all') {
            $query->where('status', $status);
        }

        if (!empty($shopId)) {
            $query->where('shop_id', (int) $shopId);
        }

        if (!empty($from)) {
            $query->whereDate('created_at', '>=', $from);
        }

        if (!empty($to)) {
            $query->whereDate('created_at', '<=', $to);
        }
    }

    private function statusOptions(array $statusCounts): array
    {
        $base = [
            Order::STATUS_PENDING => 'Pending',
            Order::STATUS_PROCESSING => 'Processing',
            Order::STATUS_SHIPPED => 'Shipped',
            Order::STATUS_DELIVERED => 'Delivered',
            Order::STATUS_COMPLETED => 'Completed',
            Order::STATUS_CANCELLED => 'Cancelled',
            'canceled' => 'Canceled',
            Order::STATUS_REFUNDED => 'Refunded',
            Order::STATUS_RETURNED => 'Returned',
        ];

        foreach (array_keys($statusCounts) as $status) {
            if (!isset($base[$status])) {
                $base[$status] = ucfirst(str_replace('_', ' ', (string) $status));
            }
        }

        return $base;
    }
}
