<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderMessage;
use Illuminate\Http\Request;

class OrderMessageController extends Controller
{
    /**
     * Show the chat interface for a given order.
     */
    public function show(Order $order)
    {
  
        return view('orders.chat', compact('order'));
    }

    /**
     * Fetch all messages for the order (JSON).
     */
    // In fetch() method
    public function fetch(Order $order, Request $request)
    {
        $query = $order->messages()->with('user:id,name')->orderBy('id');
        if ($request->has('after')) {
            $query->where('id', '>', $request->after);
        }
        $messages = $query->get()->values();
        return response()->json($messages);
    }


    /**
     * Store a new chat message for the order.
     */
    public function send(Request $request, Order $order)
    {
       

        $data = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $msg = $order->messages()->create([
            'user_id' => auth()->id(),
            'body'    => $data['body'],
        ]);

        // Create activity record for the seller
        Activity::create([
            'user_id' => $order->shop->user->id,
            'is_read' => false,
            'description' => 'You received a new message from ' . auth()->user()->name
        ]);

        return response()->json([
            'id'         => $msg->id,
            'body'       => $msg->body,
            'user'       => auth()->user()->only('id','name'),
            'created_at' => $msg->created_at->toDateTimeString(),
        ]);
    }
}
