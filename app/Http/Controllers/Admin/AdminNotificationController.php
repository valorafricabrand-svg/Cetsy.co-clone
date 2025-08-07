<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::latest()->paginate(20);
        return view('admin.notifications.index', compact('notifications'));
    }

    public function create()
    {
        return view('admin.notifications.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'   => 'required|max:255',
            'message' => 'required',
            'link'    => 'nullable|url|max:255',
        ]);

        Notification::create([
            'title'   => $request->title,
            'message' => $request->message,
            'icon'    => $request->icon,
            'link'    => $request->link,
            'is_read' => false,
        ]);

        return redirect()->route('admin.notifications.index')->with('success', 'Notification sent!');
    }
}