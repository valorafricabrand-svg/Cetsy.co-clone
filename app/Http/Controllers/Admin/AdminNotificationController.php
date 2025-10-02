<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminNotificationController extends Controller
{
    public function index()
    {
        $currentDate = now()->format('Y-m-d H:i:s');
        $user = Auth::user();

        // Get all activities without filtering by type
        $notifications = Activity::latest()->paginate(15);

        // Pass $user for NotificationRouteService usage in view
        return view('admin.notifications.index', compact(
            'notifications',
            'currentDate',
            'user'
        ));
    }
    
    public function markAsRead($id)
    {
        $notification = Activity::findOrFail($id);
        $notification->update(['is_read' => true]);
        
        return redirect()->back()->with('success', 'Notification marked as read');
    }
    
    public function markAllAsRead()
    {
        Activity::where('is_read', false)->update(['is_read' => true]);
        
        return redirect()->back()->with('success', 'All notifications marked as read');
    }
}
