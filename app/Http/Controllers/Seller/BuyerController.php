<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\User;

class BuyerController extends Controller
{
    /**
     * Display a listing of buyers who have purchased from this seller.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->shop) {
            return redirect()->route('seller.shop.create')
                ->with('warning', 'Please create a shop to continue.');
        }

        // Get all orders that contain products from this seller's shop
        $orders = Order::whereHas('items.product', function ($query) use ($user) {
            $query->where('shop_id', $user->shop->id);
        })
        ->with(['customer.shop', 'items.product'])
        ->orderBy('created_at', 'desc')
        ->get();

        // Get unique buyers with their order statistics
        $buyers = $orders->groupBy('user_id')->map(function ($userOrders) {
            $firstOrder = $userOrders->first();
            $totalSpent = $userOrders->sum(function ($order) {
                return $order->items->sum(function ($item) {
                    return $item->quantity * $item->price;
                });
            });
            
            return [
                'customer' => $firstOrder->customer,
                'total_orders' => $userOrders->count(),
                'total_spent' => $totalSpent,
                'last_order_date' => $userOrders->max('created_at'),
                'first_order_date' => $userOrders->min('created_at'),
            ];
        })->sortByDesc('total_spent');

        return view('seller.buyers.index', compact('buyers'));
    }

    /**
     * Show details of a specific buyer.
     *
     * @param int $buyerId
     * @return \Illuminate\View\View
     */
    public function show($buyerId)
    {
        $user = Auth::user();

        if (!$user->shop) {
            return redirect()->route('seller.shop.create')
                ->with('warning', 'Please create a shop to continue.');
        }

        $buyer = User::with('shop')->findOrFail($buyerId);

        // Get all orders from this buyer that contain products from this seller's shop
        $orders = Order::where('user_id', $buyerId)
            ->whereHas('items.product', function ($query) use ($user) {
                $query->where('shop_id', $user->shop->id);
            })
            ->with(['items.product', 'payment'])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalSpent = $orders->sum(function ($order) {
            return $order->items->sum(function ($item) {
                return $item->quantity * $item->price;
            });
        });

        return view('seller.buyers.show', compact('buyer', 'orders', 'totalSpent'));
    }
} 
