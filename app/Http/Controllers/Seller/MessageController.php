<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use App\Mail\MessageReceivedMail;
use Illuminate\Support\Facades\Mail;

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

        // Mark as read if not already
        if (!$message->is_read) {
            $message->is_read = true;
            $message->save();
        }

        // Get all messages in this conversation (same product and between same users)
        $conversationMessages = Message::where('product_id', $message->product_id)
            ->where(function($query) use ($message) {
                $query->where(function($q) use ($message) {
                    $q->where('sender_id', $message->sender_id)
                      ->where('receiver_id', $message->receiver_id);
                })->orWhere(function($q) use ($message) {
                    $q->where('sender_id', $message->receiver_id)
                      ->where('receiver_id', $message->sender_id);
                });
            })
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('seller.messages.show', compact('message', 'conversationMessages'));
    }

    public function reply(Request $request, $id)
    {
        $originalMessage = Message::findOrFail($id);
        
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        // Create the reply message
        $replyMessage = Message::create([
            'sender_id' => auth()->id(), // Seller is the sender
            'receiver_id' => $originalMessage->sender_id, // Buyer is the receiver
            'product_id' => $originalMessage->product_id,
            'body' => $data['message'],
        ]);

        // Send email notification to the buyer
        try {
            $buyer = User::find($originalMessage->sender_id);
            $product = $originalMessage->product;
            
            if ($buyer) {
                Mail::to($buyer->email)
                    ->send(new MessageReceivedMail(
                        $replyMessage,
                        $product,
                        auth()->user(), // Seller
                        $buyer
                    ));
            }
        } catch (\Exception $e) {
            // Log the error but don't break the user experience
            \Log::error('Failed to send message notification email: ' . $e->getMessage());
        }

        return redirect()->route('seller.messages.show', $id)
            ->with('success', 'Reply sent successfully!');
    }
} 