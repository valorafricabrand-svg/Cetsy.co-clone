<?php

// app/Http/Controllers/MessageController.php
namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'receiver_id' => ['required', 'exists:users,id'],
            'product_id'  => ['nullable', 'exists:products,id'],
            'message'     => ['required', 'string', 'max:2000'],
        ]);

        Message::create([
            'sender_id'   => $request->user()->id,
            'receiver_id' => $data['receiver_id'],
            'product_id'  => $data['product_id'] ?? null,
            'body'        => $data['message'],
        ]);

        return back()->with('success', 'Message sent to seller!');
    }
}
