<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;  // Assuming you have a Message model
use Illuminate\Http\Request;

class AdminMessageController extends Controller
{
    /**
     * Display the admin's messages.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get the authenticated admin user
        $user = auth()->user();

        // Fetch messages for the admin
        // You may want to filter messages, for example, unread messages or only messages relevant to the admin
        $messages = Message::where('receiver_id', $user->id) // Assuming admin has messages assigned to them
            ->orderBy('created_at', 'desc')->get();


        // Return the view with messages
        return view('admin.messages.index', compact('messages', 'user'));
    }
    public function reply(Request $request, $id)
    {
        // Fetch the message
        $message = Message::findOrFail($id);

        // Process the reply (save it to the database or send it as needed)
        $reply = new Message();
        $reply->sender_id = auth()->id();  // The current user (admin)
        $reply->receiver_id = $message->sender_id;  // Send reply to the original sender
        $reply->body = $request->input('reply');
        $reply->save();

        // Redirect back with success message
        return redirect()->route('admin.messages.index')->with('success', 'Reply sent successfully!');
    }
}
