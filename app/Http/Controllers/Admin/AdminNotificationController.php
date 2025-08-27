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
        $currentUser = Auth::user();
        
        // Get all activities without filtering by type
        // Modify this query to match your needs based on existing columns
        $notifications = Activity::latest()->paginate(15);
            
        return view('admin.notifications.index', compact(
            'notifications', 
            'currentDate',
            'currentUser'
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