<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $shop = $user->shop;

        // If the seller doesn't have a shop, handle gracefully
        if (!$shop) {
            return redirect()->route('seller.shop.create')
                ->with('warning', 'Please create a shop to view messages.');
        }

        // Get all product IDs for this shop
        $productIds = $shop->products()->pluck('id');

        // Get all messages for these products
        $query = Message::whereIn('product_id', $productIds)->with(['product', 'sender']);
        if ($request->filled('product')) {
            $query->where('product_id', $request->product);
        }
        $messages = $query->get();

        return view('seller.messages.index', compact('messages'));
    }

    public function show($id)
    {
        $message = Message::with(['product', 'sender'])->findOrFail($id);
        return view('seller.messages.show', compact('message'));
    }
} 