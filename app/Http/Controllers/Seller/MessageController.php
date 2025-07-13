<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use App\Models\Product;
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

        // Get conversations where the seller is either sender or receiver
        $conversations = Message::whereIn('product_id', $productIds)
            ->where(function($query) use ($user) {
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

        return view('seller.messages.index', compact('conversations'));
    }

    public function show(Request $request, $conversationId)
    {
        $user = auth()->user();
        $shop = $user->shop;

        if (!$shop) {
            return redirect()->route('seller.shop.create')
                ->with('warning', 'Please create a shop to view messages.');
        }
        
        // Parse conversation ID (format: product_id-user_id)
        $parts = explode('-', $conversationId);
        if (count($parts) !== 2) {
            abort(404, 'Invalid conversation ID');
        }
        
        $productId = $parts[0];
        $otherUserId = $parts[1];
        
        // Validate that the product belongs to the seller's shop
        $product = Product::find($productId);
        if (!$product || $product->shop_id !== $shop->id) {
            abort(403, 'You are not authorized to view this conversation.');
        }
        
        // Validate that the user is part of this conversation
        $conversationExists = Message::where('product_id', $productId)
            ->where(function($query) use ($user, $otherUserId) {
                $query->where(function($q) use ($user, $otherUserId) {
                    $q->where('sender_id', $user->id)->where('receiver_id', $otherUserId);
                })->orWhere(function($q) use ($user, $otherUserId) {
                    $q->where('sender_id', $otherUserId)->where('receiver_id', $user->id);
                });
            })
            ->exists();
            
        if (!$conversationExists) {
            abort(403, 'You are not authorized to view this conversation.');
        }

        // Get all messages for this conversation
        $messages = Message::where('product_id', $productId)
            ->where(function($query) use ($user, $otherUserId) {
                $query->where(function($q) use ($user, $otherUserId) {
                    $q->where('sender_id', $user->id)->where('receiver_id', $otherUserId);
                })->orWhere(function($q) use ($user, $otherUserId) {
                    $q->where('sender_id', $otherUserId)->where('receiver_id', $user->id);
                });
            })
            ->with(['sender:id,name', 'receiver:id,name', 'product'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read where current user is receiver
        $messages->where('receiver_id', $user->id)->each(function($message) {
            if (!$message->is_read) {
                $message->update(['is_read' => true]);
            }
        });

        $otherUser = User::find($otherUserId);

        return view('seller.messages.show', compact('messages', 'otherUser', 'product', 'conversationId'));
    }

    public function markAsRead($id)
    {
        $message = Message::findOrFail($id);
        $currentUser = auth()->user();
        
        // Ensure the current user is the receiver (seller) of this message
        if ($message->receiver_id !== $currentUser->id) {
            abort(403, 'Unauthorized action.');
        }

        // Also ensure the message belongs to one of the seller's products
        $shop = $currentUser->shop;
        if (!$shop) {
            abort(403, 'Shop not found.');
        }

        $productIds = $shop->products()->pluck('id');
        if (!in_array($message->product_id, $productIds->toArray())) {
            abort(403, 'Message does not belong to your products.');
        }

        $message->is_read = true;
        $message->save();

        return back()->with('success', 'Message marked as read.');
    }

    public function bulkMarkAsRead()
    {
        $user = auth()->user();
        $shop = $user->shop;

        if (!$shop) {
            return redirect()->route('seller.shop.create')
                ->with('warning', 'Please create a shop to manage messages.');
        }

        // Get all product IDs for this shop
        $productIds = $shop->products()->pluck('id');

        // Mark all unread messages as read (only for messages where seller is receiver)
        $updatedCount = Message::whereIn('product_id', $productIds)
            ->where('receiver_id', $user->id) // Only messages where seller is the receiver
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', "Successfully marked {$updatedCount} message(s) as read.");
    }

    public function reply(Request $request, $conversationId)
    {
        $user = auth()->user();
        $shop = $user->shop;

        if (!$shop) {
            abort(403, 'Shop not found.');
        }

        // Parse conversation ID (format: product_id-user_id)
        $parts = explode('-', $conversationId);
        if (count($parts) !== 2) {
            abort(404, 'Invalid conversation ID');
        }
        
        $productId = $parts[0];
        $otherUserId = $parts[1];
        
        // Validate that the product belongs to the seller's shop
        $product = Product::find($productId);
        if (!$product || $product->shop_id !== $shop->id) {
            abort(403, 'You are not authorized to reply to this conversation.');
        }
        
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        // Create the reply message
        $replyMessage = Message::create([
            'sender_id' => $user->id, // Seller is the sender
            'receiver_id' => $otherUserId, // Buyer is the receiver
            'product_id' => $productId,
            'body' => $data['message'],
        ]);

        // Send email notification to the buyer
        try {
            $buyer = User::find($otherUserId);
            
            if ($buyer) {
                Mail::to($buyer->email)
                    ->send(new MessageReceivedMail(
                        $replyMessage,
                        $product,
                        $user, // Seller
                        $buyer
                    ));
            }
        } catch (\Exception $e) {
            // Log the error but don't break the user experience
            \Log::error('Failed to send message notification email: ' . $e->getMessage());
        }

        return redirect()->route('seller.messages.show', $conversationId)
            ->with('success', 'Reply sent successfully!');
    }
} 