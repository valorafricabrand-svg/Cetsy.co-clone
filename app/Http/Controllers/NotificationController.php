<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $notificationQuery = Activity::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        $notifications = (clone $notificationQuery)
            ->paginate(20)
            ->withQueryString();

        $unreadCount = (clone $notificationQuery)
            ->where('is_read', false)
            ->count();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('notifications.partials.items', [
                    'notifications' => $notifications,
                    'notificationsIndexUrl' => $request->url(),
                ])->render(),
                'next_page_url' => $notifications->nextPageUrl(),
                'has_more_pages' => $notifications->hasMorePages(),
                'shown_count' => $notifications->lastItem() ?? 0,
                'total_count' => $notifications->total(),
                'unread_count' => $unreadCount,
            ]);
        }

        return view('notifications.index', compact('notifications', 'unreadCount'));
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

    /**
     * Polling payload for in-app alerts.
     */
    public function pulse()
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $notificationQuery = Activity::query();
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            $notificationQuery->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)->orWhereNull('user_id');
            });
        } else {
            $notificationQuery->where('user_id', $user->id);
        }

        $unreadNotificationsQuery = (clone $notificationQuery)->where('is_read', false);
        $unreadMessagesQuery = Message::query()
            ->where('receiver_id', $user->id)
            ->where('is_read', false);

        $latestNotificationModel = (clone $unreadNotificationsQuery)
            ->latest('id')
            ->first();
        $latestMessageModel = (clone $unreadMessagesQuery)
            ->with(['sender:id,name'])
            ->latest('id')
            ->first();

        return response()->json([
            'notif' => (int) (clone $unreadNotificationsQuery)->count(),
            'msg' => (int) (clone $unreadMessagesQuery)->count(),
            'latest_notification' => $this->serializeActivityForPulse($latestNotificationModel, $user),
            'latest_message' => $this->serializeMessageForPulse($latestMessageModel, $user),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    private function serializeActivityForPulse(?Activity $activity, $user): ?array
    {
        if (! $activity) {
            return null;
        }

        $title = trim((string) ($activity->title ?: $activity->description ?: $activity->message ?: 'Notification'));
        $description = trim((string) ($activity->description ?: $activity->message ?: $activity->title ?: ''));
        $link = route('notifications.index');
        $action = 'Open';

        try {
            $link = \App\Services\NotificationRouteService::getRouteForNotification($activity, $user) ?: $link;
        } catch (\Throwable $e) {
            $link = route('notifications.index');
        }

        try {
            $action = \App\Services\NotificationRouteService::getLinkText($activity, $user) ?: $action;
        } catch (\Throwable $e) {
            $action = 'Open';
        }

        return [
            'id' => (int) $activity->id,
            'type' => (string) ($activity->type ?? ''),
            'title' => $title,
            'description' => $description,
            'link' => $link,
            'action' => $action,
            'created_at' => optional($activity->created_at)->toIso8601String(),
        ];
    }

    private function serializeMessageForPulse(?Message $message, $user): ?array
    {
        if (! $message) {
            return null;
        }

        $activity = new Activity([
            'type' => Activity::TYPE_MESSAGE,
            'related_id' => $message->id,
        ]);

        $link = route('notifications.index');
        try {
            $link = \App\Services\NotificationRouteService::getRouteForNotification($activity, $user) ?: $link;
        } catch (\Throwable $e) {
            $link = route('notifications.index');
        }

        return [
            'id' => (int) $message->id,
            'sender_name' => trim((string) (optional($message->sender)->name ?: 'New message')),
            'body_preview' => Str::limit(trim((string) ($message->body ?? '')), 120),
            'link' => $link,
            'created_at' => optional($message->created_at)->toIso8601String(),
        ];
    }
}
