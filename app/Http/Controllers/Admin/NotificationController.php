<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity; // Use your activities table model
use App\Models\User; // For causer relationship

class NotificationController extends Controller
{
    public function index()
    {
        // Query all activities for the admin notifications page
        $notifications = Activity::with('causer')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function markAllRead()
    {
        Activity::where('is_read', false)->update(['is_read' => true]);
        return redirect()->route('admin.notifications.index')->with('success', 'All notifications marked as read.');
    }

public function markRead($id)
{
    $notification = Activity::findOrFail($id); // Activity is your model for activities table
    $notification->is_read = true;
    $notification->save();

    return redirect()->route('admin.notifications.index')->with('success', 'Notification marked as read.');
}
}