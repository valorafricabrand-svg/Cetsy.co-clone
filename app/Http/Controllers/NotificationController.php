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

        if ($request->wantsJson() || $request->ajax() || $request->headers->get('Accept') === 'application/json') {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();

        Activity::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if ($request->wantsJson() || $request->ajax() || $request->headers->get('Accept') === 'application/json') {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Mark a notification as read and redirect to its target page.
     */
    public function open($id)
    {
        $user = Auth::user();
        $activity = Activity::findOrFail($id);

        // Allow if this belongs to the user or is a global admin notification
        $isOwner = (int) $activity->user_id === (int) $user->id;
        $isAdminGlobal = is_null($activity->user_id) && method_exists($user, 'isAdmin') && $user->isAdmin();
        if (!($isOwner || $isAdminGlobal)) {
            abort(403);
        }

        // Mark as read (idempotent)
        if (!$activity->is_read) {
            $activity->is_read = true;
            $activity->save();
        }

        // Resolve destination with basic pre-authorization checks to avoid 403s
        $route = null;
        try {
            $type = $activity->type ?? 'general';
            if ($type === \App\Models\Activity::TYPE_ORDER && $activity->related_id) {
                $order = \App\Models\Order::with('shop')->find((int) $activity->related_id);
                if ($order) {
                    $isBuyer = (int)$order->user_id === (int)$user->id;
                    $isSeller = (int)optional($order->shop)->user_id === (int)$user->id;
                    if (!($isBuyer || $isSeller || (method_exists($user,'isAdmin') && $user->isAdmin()))) {
                        return redirect()->route('notifications.index');
                    }
                }
            } elseif ($type === \App\Models\Activity::TYPE_DISPUTE && $activity->related_id) {
                $dispute = \App\Models\Dispute::find((int) $activity->related_id);
                if ($dispute) {
                    $isParty = (int)$dispute->buyer_id === (int)$user->id || (int)$dispute->seller_id === (int)$user->id;
                    $isAdmin = method_exists($user,'isAdmin') && $user->isAdmin();
                    if (!($isParty || $isAdmin)) {
                        return redirect()->route('notifications.index');
                    }
                }
            }
        } catch (\Throwable $e) {}

        // Resolve destination
        try {
            $route = $route ?: \App\Services\NotificationRouteService::getRouteForNotification($activity, $user);
        } catch (\Throwable $e) {
            $route = null;
        }

        if (!$route) {
            $route = route('notifications.index');
        }

        return redirect()->to($route);
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
