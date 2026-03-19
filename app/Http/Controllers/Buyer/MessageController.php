<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use App\Models\Product;
use App\Models\Offer;
use App\Mail\MessageReceivedMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Activity;

class MessageController extends Controller
{
    public function buyerIndex(Request $request)
    {
        $user = auth()->user();

        // Get conversations where the buyer is either sender or receiver
        $conversations = Message::where(function($query) use ($user) {
            $query->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
        })
        ->with(['product', 'sender', 'receiver'])
        ->orderBy('created_at', 'desc')
        ->get()
        ->groupBy(function($message) use ($user) {
            // Group by product and the other participant
            $otherUserId = $message->sender_id == $user->id ? $message->receiver_id : $message->sender_id;
            return $message->product_id . '-' . $otherUserId;
        })
        ->map(function($messages) use ($user) {
            // Get the latest message and other participant info
            $latestMessage = $messages->first();
            $otherUserId = $latestMessage->sender_id == $user->id ? $latestMessage->receiver_id : $latestMessage->sender_id;
            $otherUser = $latestMessage->sender_id == $user->id ? $latestMessage->receiver : $latestMessage->sender;
            
            return [
                'latest_message' => $latestMessage,
                'other_user' => $otherUser,
                'product' => $latestMessage->product,
                'unread_count' => $messages->where('receiver_id', $user->id)->where('is_read', false)->count(),
                'total_messages' => $messages->count(),
                'conversation_id' => $latestMessage->product_id . '-' . $otherUserId
            ];
        })
        ->sortByDesc('latest_message.created_at');

        // Filter by product if specified
        if ($request->filled('product')) {
            $conversations = $conversations->filter(function($conversation) use ($request) {
                return $conversation['product'] && $conversation['product']->id == $request->product;
            });
        }

        // Filter by read status
        if ($request->filter === 'unread') {
            $conversations = $conversations->filter(function($conversation) {
                return $conversation['unread_count'] > 0;
            });
        }

        // Search by user, product, or message
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $conversations = $conversations->filter(function($conversation) use ($search) {
                $userMatch = $conversation['other_user'] && (
                    str_contains(strtolower($conversation['other_user']->name), $search) ||
                    str_contains(strtolower($conversation['other_user']->email), $search)
                );
                $productMatch = $conversation['product'] && str_contains(strtolower($conversation['product']->name), $search);
                $messageMatch = $conversation['latest_message'] && str_contains(strtolower($conversation['latest_message']->body), $search);
                return $userMatch || $productMatch || $messageMatch;
            });
        }

        return view('buyer.messages.index', compact('conversations'));
    }

    public function show(Request $request, $conversationId)
    {
        $user = auth()->user();
        
        // Parse conversation ID to get product ID and other user ID
        $parts = explode('-', $conversationId);
        if (count($parts) !== 2) {
            abort(404, 'Invalid conversation ID');
        }
        
        $productId = $parts[0];
        $otherUserId = $parts[1];
        
        // Verify the current user is part of this conversation
        $messages = Message::where('product_id', $productId)
            ->where(function($query) use ($user, $otherUserId) {
                $query->where(function($q) use ($user, $otherUserId) {
                    $q->where('sender_id', $user->id)
                      ->where('receiver_id', $otherUserId);
                })->orWhere(function($q) use ($user, $otherUserId) {
                    $q->where('sender_id', $otherUserId)
                      ->where('receiver_id', $user->id);
                });
            })
            ->with(['product.media', 'product.shop.user', 'sender.shop', 'receiver.shop'])
            ->orderBy('created_at', 'asc')
            ->get();

        if ($messages->isEmpty()) {
            abort(404, 'Conversation not found');
        }

        $product = $messages->first()->product;
        $otherUser = $user->id == $otherUserId ? $user : User::with('shop')->find($otherUserId);
        $shop = $product?->shop ?? $otherUser?->shop;
        $latestOffer = $product
            ? Offer::where('product_id', $productId)
                ->where('buyer_id', $user->id)
                ->latest('updated_at')
                ->first()
            : null;

        // Mark messages as read
        Message::where('product_id', $productId)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // Also clear related notification entries (Activity -> TYPE_MESSAGE)
        try {
            \App\Models\Activity::where('user_id', $user->id)
                ->where('type', \App\Models\Activity::TYPE_MESSAGE)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        } catch (\Throwable $e) {
            // non-fatal
        }

        return view('buyer.messages.show', compact('messages', 'product', 'otherUser', 'conversationId', 'shop', 'latestOffer'));
    }

    public function reply(Request $request, $conversationId)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = auth()->user();
        
        // Parse conversation ID
        $parts = explode('-', $conversationId);
        if (count($parts) !== 2) {
            return back()->withErrors(['error' => 'Invalid conversation ID']);
        }
        
        $productId = $parts[0];
        $otherUserId = $parts[1];
        
        // Verify the current user is part of this conversation
        $existingMessage = Message::where('product_id', $productId)
            ->where(function($query) use ($user, $otherUserId) {
                $query->where(function($q) use ($user, $otherUserId) {
                    $q->where('sender_id', $user->id)
                      ->where('receiver_id', $otherUserId);
                })->orWhere(function($q) use ($user, $otherUserId) {
                    $q->where('sender_id', $otherUserId)
                      ->where('receiver_id', $user->id);
                });
            })
            ->first();

        if (!$existingMessage) {
            return back()->withErrors(['error' => 'Conversation not found']);
        }

        // Create new message
        $message = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $otherUserId,
            'product_id' => $productId,
            'body' => $request->message,
            'is_read' => false,
        ]);

        // Send email notification
        try {
            $receiver = User::find($otherUserId);
            if ($receiver) {
                Mail::to($receiver->email)->send(new MessageReceivedMail($message));
            }
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error('Failed to send message notification: ' . $e->getMessage());
        }

        // Log activity
        Activity::create([
            'user_id' => $user->id,
            'type' => 'message_sent',
            'description' => "Sent a message about {$message->product->name}",
            'related_id' => $message->id,
            'related_type' => Message::class,
        ]);

        return back()->with('success', 'Message sent successfully!');
    }

    public function markAsRead(Request $request, Message $message)
    {
        // Verify the current user is the receiver
        if ($message->receiver_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $message->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function bulkMarkAsRead(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'integer|exists:messages,id'
        ]);

        $user = auth()->user();
        
        Message::whereIn('id', $request->message_ids)
            ->where('receiver_id', $user->id)
            ->update(['is_read' => true]);

        return response()->json(['success' => true, 'message' => 'Messages marked as read']);
    }
}
