<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Fetch notifications for this admin only
        $notifications = Activity::with('causer')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.notifications.index', compact('notifications','user'));
    }

    public function markAllRead()
    {
        $user = Auth::user();
        Activity::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
        return redirect()->route('admin.notifications.index')->with('success', 'All notifications marked as read.');
    }

    public function markRead($id)
    {
        $user = Auth::user();
        $notification = Activity::where('user_id', $user->id)->findOrFail($id);
        $notification->is_read = true;
        $notification->save();

        return redirect()->route('admin.notifications.index')->with('success', 'Notification marked as read.');
    }
}
