<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;  // Assuming you have a Message model
use Illuminate\Http\Request;

class AdminMessageController extends Controller
{
    
    public function index()
    {
        
        $user = auth()->user();

    
        $messages = Message::where('receiver_id', $user->id) 
            ->orderBy('created_at', 'desc')->get();


        // Return the view with messages
        return view('seller.messages.index', compact('messages', 'user'));
    }
    public function reply(Request $request, $id)
    {
        // Fetch the message
        $message = Message::findOrFail($id);

        // Process the reply (save it to the database or send it as needed)
        $reply = new Message();
        $reply->sender_id = auth()->id();  
        $reply->receiver_id = $message->sender_id;  // Send reply to the original sender
        $reply->body = $request->input('reply');
        $reply->save();

        // Redirect back with success message
        return redirect()->route('seller.messages.index')->with('success', 'Reply sent successfully!');
    }
}
