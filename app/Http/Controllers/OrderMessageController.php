<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderMessage;
use App\Models\Activity;
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
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240'
        ]);

        // Determine message type based on user role
        $messageType = OrderMessage::TYPE_BUYER_MESSAGE; // Default
        if ($order->shop && auth()->id() === $order->shop->user_id) {
            $messageType = OrderMessage::TYPE_SELLER_MESSAGE;
        }

        // Handle file uploads
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('orders/attachments', 'public');
                $attachments[] = [
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType()
                ];
            }
        }

        $msg = $order->messages()->create([
            'user_id' => auth()->id(),
            'body' => $data['body'],
            'type' => $messageType,
            'attachments' => $attachments
        ]);

        // Create activity record for the seller
        if ($order->shop && $order->shop->user) {
            Activity::create([
                'user_id' => $order->shop->user->id,
                'is_read' => false,
                'description' => 'You received a new message from ' . auth()->user()->name,
                'type' => \App\Models\Activity::TYPE_MESSAGE,
                'related_id' => $msg->id,
                'related_type' => 'message'
            ]);
        }

        return response()->json([
            'id' => $msg->id,
            'body' => $msg->body,
            'type' => $msg->type,
            'attachments' => $msg->attachments,
            'user' => auth()->user()->only('id','name'),
            'created_at' => $msg->created_at->toDateTimeString(),
        ]);
    }
}
