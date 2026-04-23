@php
    $currentNotificationsUrl = $notificationsIndexUrl ?? request()->url();
    $notificationIndexPaths = collect([
        $currentNotificationsUrl,
        \Illuminate\Support\Facades\Route::has('notifications.index') ? route('notifications.index') : null,
        \Illuminate\Support\Facades\Route::has('buyer.notifications.index') ? route('buyer.notifications.index') : null,
        \Illuminate\Support\Facades\Route::has('seller.notifications.index') ? route('seller.notifications.index') : null,
    ])
        ->filter()
        ->map(fn ($url) => rtrim((string) parse_url($url, PHP_URL_PATH), '/'))
        ->filter()
        ->unique()
        ->values()
        ->all();
@endphp

@foreach($notifications as $notification)
    @php
        $route = \App\Services\NotificationRouteService::getRouteForNotification($notification, auth()->user());
        $linkText = \App\Services\NotificationRouteService::getLinkText($notification, auth()->user());
        $targetHref = $route;
        $targetPath = $targetHref ? rtrim((string) parse_url($targetHref, PHP_URL_PATH), '/') : null;
        $isExternalTarget = $targetPath && !in_array($targetPath, $notificationIndexPaths, true);
        $href = $isExternalTarget && \Illuminate\Support\Facades\Route::has('notifications.open')
            ? route('notifications.open', $notification->id)
            : $targetHref;
    @endphp
    <div class="notification-item {{ !$notification->is_read ? 'unread' : '' }}" id="notification-{{ $notification->id }}">
        @if(!$notification->is_read)
            <div class="new-badge">
                <i class="fas fa-star mr-1"></i>New
            </div>
        @endif

        <div class="notification-content">
            @if($isExternalTarget)
                <a href="{{ $href }}" class="notification-text {{ !$notification->is_read ? 'unread' : '' }} no-underline block">
                    {{ $notification->description }}
                </a>
            @else
                <p class="notification-text {{ !$notification->is_read ? 'unread' : '' }}">
                    {{ $notification->description }}
                </p>
            @endif

            <div class="notification-meta">
                <div class="notification-date">
                    <i class="far fa-clock"></i>
                    {{ $notification->created_at->format('M d, Y \a\t g:i A') }}
                </div>
                <span class="text-slate-500">&bull;</span>
                <span>{{ $notification->created_at->diffForHumans() }}</span>
            </div>

            <div class="notification-actions">
                @if($isExternalTarget)
                    <a href="{{ $href }}" class="btn-view" data-notif-id="{{ $notification->id }}" data-unread="{{ $notification->is_read ? 0 : 1 }}">
                        <i class="fas fa-eye"></i>
                        {{ $linkText }}
                    </a>
                @endif

                @if(!$notification->is_read)
                    <button type="button"
                            class="btn-mark-read"
                            data-notification-id="{{ $notification->id }}"
                            data-notification-description="{{ $notification->description }}">
                        <i class="fas fa-check mr-1"></i>Mark as Read
                    </button>
                @endif
            </div>
        </div>
    </div>
@endforeach
