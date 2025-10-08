<?php

// app/Http/Controllers/MessageController.php
namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Product;
use App\Models\User;
use App\Mail\MessageReceivedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Activity;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        // Convert empty string to null for product_id before validation
        if ($request->has('product_id') && $request->product_id === '') {
            $request->merge(['product_id' => null]);
        }

        $data = $request->validate([
            'receiver_id' => ['required', 'exists:users,id'],
            'product_id'  => ['nullable', 'exists:products,id'],
            'message'     => ['required', 'string', 'max:2000'],
        ]);

        $message = Message::create([
            'sender_id'   => $request->user()->id,
            'receiver_id' => $data['receiver_id'],
            'product_id'  => $data['product_id'] ?? null,
            'body'        => $data['message'],
        ]);

        // Get receiver and product info
        $receiver = User::find($data['receiver_id']);
        $product = null;
        
        if ($data['product_id']) {
            $product = Product::find($data['product_id']);
        }
        
        if ($receiver) {
            // Create activity record for the seller (always create for any message)
            Activity::create([
                'user_id' => $receiver->id,
                'is_read' => false,
                'description' => 'You received a new message from ' . $request->user()->name,
                'type' => \App\Models\Activity::TYPE_MESSAGE,
                'related_id' => $message->id,
                'related_type' => 'message'
            ]);

            // Log sender activity (keep in log) with optional source context
            $senderDesc = 'You messaged ' . ($receiver->name ?? 'a buyer');
            if ($product) {
                $senderDesc .= ' about "' . $product->name . '"';
            }
            $props = [];
            if ($request->filled('source')) {
                $props['source'] = $request->input('source');
            }
            Activity::create([
                'user_id' => $request->user()->id,
                'is_read' => true,
                'description' => $senderDesc,
                'type' => \App\Models\Activity::TYPE_MESSAGE,
                'related_id' => $message->id,
                'related_type' => 'message',
                'properties' => $props,
            ]);

            // Send email to receiver (shop owner)
            try {
                Mail::to($receiver->email)
                    ->send(new MessageReceivedMail(
                        $message,
                        $product,
                        $request->user(),
                        $receiver
                    ));
            } catch (\Exception $e) {
                // Log the error but don't break the user experience
                \Log::error('Failed to send message notification email: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Message sent successfully!');
    }

    public function buyerIndex(Request $request)
    {
        $user = auth()->user();

        // Get conversations where the buyer is either sender or receiver
        $conversations = Message::where(function($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->with(['product.shop', 'sender.shop', 'receiver.shop'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function($message) use ($user) {
                // Group by product and the other participant
                $otherUserId = $message->sender_id == $user->id ? $message->receiver_id : $message->sender_id;
                return (($message->product_id ?? 0)) . '-' . $otherUserId;
            })
            ->map(function($messages) use ($user) {
                // Get the latest message and other participant info
                $latestMessage = $messages->first();
                $otherUserId = $latestMessage->sender_id == $user->id ? $latestMessage->receiver_id : $latestMessage->sender_id;
                $otherUser = $latestMessage->sender_id == $user->id ? $latestMessage->receiver : $latestMessage->sender;
                // Get shop info for the other user (seller)
                $shop = $otherUser->shop;
                return [
                    'latest_message' => $latestMessage,
                    'other_user' => $otherUser,
                    'shop' => $shop,
                    'product' => $latestMessage->product,
                    'unread_count' => $messages->where('receiver_id', $user->id)->where('is_read', false)->count(),
                    'total_messages' => $messages->count(),
                    'conversation_id' => (($latestMessage->product_id ?? 0)) . '-' . $otherUserId
                ];
            })
            ->sortByDesc('latest_message.created_at');

        // Filter by product if specified
        if ($request->filled('product')) {
            $conversations = $conversations->filter(function($conversation) use ($request) {
                return $conversation['product'] && $conversation['product']->id == $request->product;
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
        
        // Parse conversation ID (format: product_id-user_id)
        $parts = explode('-', $conversationId);
        if (count($parts) !== 2) {
            abort(404, 'Invalid conversation ID');
        }
        
        $productId = $parts[0];
        $otherUserId = $parts[1];
        
        // Validate that the user is part of this conversation
        $conversationExists = Message::where(function($q) use ($productId){
                if ((int)$productId === 0) { $q->whereNull('product_id'); }
                else { $q->where('product_id', $productId); }
            })
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
        $messages = Message::where(function($q) use ($productId){
                if ((int)$productId === 0) { $q->whereNull('product_id'); }
                else { $q->where('product_id', $productId); }
            })
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

        // Clear related notification entries for messages so badges update
        try {
            \App\Models\Activity::where('user_id', $user->id)
                ->where('type', \App\Models\Activity::TYPE_MESSAGE)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        } catch (\Throwable $e) {
            // ignore
        }

        $otherUser = User::find($otherUserId);
        $product = (int)$productId === 0 ? null : Product::find($productId);
        $shop = $product && $product->shop ? $product->shop : ($otherUser ? $otherUser->shop : null);

        return view('buyer.messages.show', compact('messages', 'otherUser', 'product', 'conversationId', 'shop'));
    }

    public function sellerIndex(Request $request)
    {
        $user = auth()->user();
        
        // Get conversations where the seller is either sender or receiver
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

        return view('seller.messages.index', compact('conversations'));
    }

    public function sellerShow(Request $request, $conversationId)
    {
        $user = auth()->user();
        
        // Parse conversation ID (format: product_id-user_id)
        $parts = explode('-', $conversationId);
        if (count($parts) !== 2) {
            abort(404, 'Invalid conversation ID');
        }
        
        $productId = $parts[0];
        $otherUserId = $parts[1];
        
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
        $product = Product::find($productId);

        return view('seller.messages.show', compact('messages', 'otherUser', 'product', 'conversationId'));
    }
}
