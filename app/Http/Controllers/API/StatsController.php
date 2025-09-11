<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;

class StatsController extends Controller
{
    /**
     * Seller stats: product count and pending orders for the seller's shop.
     */
    public function seller(Request $request)
    {
        $user = $request->user();
        if (! $user) return response()->json(['message' => 'Unauthorized'], 401);
        if (! method_exists($user, 'isSeller') || ! $user->isSeller()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        if (! $user->shop) {
            return response()->json(['product_count' => 0, 'pending_orders' => 0]);
        }

        $shopId = $user->shop->id;
        $productCount = Product::where('shop_id', $shopId)->count();
        $pending = Order::where('shop_id', $shopId)
            ->whereIn('status', [
                Order::STATUS_PENDING,
                Order::STATUS_PROCESSING,
            ])->count();

        return response()->json([
            'product_count' => $productCount,
            'pending_orders' => $pending,
        ]);
    }
}

