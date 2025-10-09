<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $notifications = Activity::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);

        Log::info('Mark as read called', [
            'activity_id' => $activity->id,
            'user_id' => Auth::id(),
            'activity_user_id' => $activity->user_id
        ]);

        // Ensure the notification belongs to the authenticated user
        if ($activity->user_id !== Auth::id()) {
            abort(403);
        }

        $activity->update(['is_read' => true]);

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead()
    {
        $user = Auth::user();

        Activity::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Lightweight unread counters for navbar badges (AJAX JSON).
     */
    public function counts()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['notif' => 0, 'msg' => 0]);
        }

        // Notifications (Activity) — admins also see global (user_id null) entries
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            $notif = Activity::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhereNull('user_id');
            })->where('is_read', false)->count();
        } else {
            $notif = Activity::where('user_id', $user->id)->where('is_read', false)->count();
        }

        // Messages (unread as receiver)
        try {
            $msg = \App\Models\Message::where('receiver_id', $user->id)->where('is_read', false)->count();
        } catch (\Throwable $e) {
            $msg = 0;
        }

        return response()->json([
            'notif' => (int) $notif,
            'msg'   => (int) $msg,
        ]);
    }
}
