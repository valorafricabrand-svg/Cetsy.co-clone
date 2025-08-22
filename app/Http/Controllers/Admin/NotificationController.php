<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Show the notifications for admin.
     */
    public function index()
    {
        // Fetch notifications for the current admin user
        // Adjust this if you want to show all notifications, or only unread, etc.
        $notifications = auth()->user()->notifications()->paginate(20);

        // Pass notifications to the view
        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request)
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    }
}