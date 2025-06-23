<?php

// app/Http/Controllers/MessageController.php
namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Product;
use App\Models\User;
use App\Mail\MessageReceivedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MessageController extends Controller
{
    

    public function store(Request $request)
    {
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

        // Send email to receiver (shop owner)
        try {
            $receiver = User::find($data['receiver_id']);
            $product = null;
            
            if ($data['product_id']) {
                $product = Product::find($data['product_id']);
            }
            
            if ($receiver) {
                Mail::to($receiver->email)
                    ->send(new MessageReceivedMail(
                        $message,
                        $product,
                        $request->user(),
                        $receiver
                    ));
            }
        } catch (\Exception $e) {
            // Log the error but don't break the user experience
            \Log::error('Failed to send message notification email: ' . $e->getMessage());
        }

        return back()->with('success', 'Message sent to seller!');
    }

    public function buyerIndex(Request $request)
    {
        $user = auth()->user();

        // Get all messages where the buyer is the receiver
        $query = Message::where('sender_id', $user->id)->with(['product', 'sender']);

        // Optional: filter by product
        if ($request->filled('product')) {
            $query->where('product_id', $request->product);
        }

        $messages = $query->orderBy('id', 'desc')->get();

        return view('buyer.messages.index', compact('messages'));
    }

    public function show(Message $message)
    {
        $messages = $message->with('sender:id,name')->orderBy('id')->get();
        return view('buyer.messages.show', compact('message', 'messages'));
    }

   
}
