<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Display a listing of conversations
     */
    public function index()
    {
        $conversations = Conversation::with(['buyer', 'seller', 'lastMessage'])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('admin.messages.index', compact('conversations'));
    }

    /**
     * Display the specified conversation
     */
    public function show(Conversation $conversation)
    {
        $conversation->load(['buyer', 'seller', 'messages.user']);
        
        $messages = $conversation->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.messages.show', compact('conversation', 'messages'));
    }
}
