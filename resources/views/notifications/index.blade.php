@extends('theme.'.theme().'.layouts.app')

@section('title', 'Notifications')

@section('styles')
<style>
    :root {
        --primary-green: #25B003;
        --secondary-green: #198754;
        --success-light: rgba(37, 176, 3, 0.1);
        --light-gray: #f8f9fa;
        --border-color: #e9ecef;
        --text-dark: #212529;
        --text-muted: #6c757d;
        --shadow-sm: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        --shadow-md: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 1rem 3rem rgba(0, 0, 0, 0.175);
        --border-radius: 0.75rem;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    * {
        box-sizing: border-box;
    }

    .notifications-container {
        max-width: 1900px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    /* Modern Header */
    .page-header {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
        color: white;
        padding: 2.5rem 2rem;
        border-radius: var(--border-radius);
        margin-bottom: 2rem;
        box-shadow: var(--shadow-lg);
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(5deg); }
    }

    .header-content {
        position: relative;
        z-index: 1;
    }

    .page-title {
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 0.5rem 0;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .page-title i {
        font-size: 2rem;
        animation: ring 2s ease-in-out infinite;
    }

    @keyframes ring {
        0%, 100% { transform: rotate(0deg); }
        10%, 30% { transform: rotate(-15deg); }
        20%, 40% { transform: rotate(15deg); }
        50% { transform: rotate(0deg); }
    }

    .header-stats {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        margin-top: 1rem;
        font-size: 0.95rem;
        opacity: 0.95;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255, 255, 255, 0.15);
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        backdrop-filter: blur(10px);
    }

    .mark-all-btn {
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 2rem;
        font-weight: 600;
        transition: var(--transition);
        backdrop-filter: blur(10px);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .mark-all-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.5);
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        color: white;
    }

    /* Modern Card */
    .notifications-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-md);
        overflow: hidden;
        border: 1px solid var(--border-color);
    }

    /* Notification Items */
    .notification-item {
        padding: 1.75rem;
        border-bottom: 1px solid var(--border-color);
        transition: var(--transition);
        position: relative;
        background: white;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-item.unread {
        background: linear-gradient(90deg, var(--success-light) 0%, white 100%);
        border-left: 4px solid var(--primary-green);
    }

    .notification-item.unread::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background: linear-gradient(180deg, var(--primary-green), var(--secondary-green));
    }

    .notification-item:hover {
        background: var(--light-gray);
        transform: translateX(8px);
        box-shadow: var(--shadow-sm);
    }

    .notification-content {
        display: flex;
        gap: 1.5rem;
        align-items: flex-start;
    }

    .notification-icon {
        width: 48px;
        height: 48px;
        min-width: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        box-shadow: 0 4px 12px rgba(37, 176, 3, 0.3);
    }

    .notification-icon.unread {
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); box-shadow: 0 4px 12px rgba(37, 176, 3, 0.3); }
        50% { transform: scale(1.05); box-shadow: 0 6px 20px rgba(37, 176, 3, 0.5); }
    }

    .notification-details {
        flex: 1;
        min-width: 0;
    }

    .notification-text {
        font-size: 1rem;
        line-height: 1.6;
        color: var(--text-dark);
        margin: 0 0 0.75rem 0;
        word-wrap: break-word;
    }

    .notification-text.unread {
        font-weight: 600;
        color: #1a202c;
    }

    .notification-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        font-size: 0.875rem;
        color: var(--text-muted);
        margin-bottom: 1rem;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    .meta-divider {
        width: 4px;
        height: 4px;
        background: var(--text-muted);
        border-radius: 50%;
        opacity: 0.5;
    }

    .notification-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .btn-view {
        background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
        border: none;
        color: white;
        padding: 0.375rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.8rem;
        font-weight: 600;
        transition: var(--transition);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        box-shadow: 0 1px 4px rgba(37, 176, 3, 0.25);
        line-height: 1.2;
    }

    .btn-view:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 12px rgba(37, 176, 3, 0.28);
        color: white;
        text-decoration: none;
    }

    .btn-mark-read {
        background: white;
        border: 1px solid var(--border-color);
        color: var(--text-muted);
        padding: 0.375rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.8rem;
        font-weight: 600;
        transition: var(--transition);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        line-height: 1.2;
    }

.btn-mark-read:hover {
        border-color: var(--primary-green);
        color: var(--primary-green);
        background: var(--success-light);
        transform: translateY(-1px);
    }

    /* On wider screens, align actions to the right for compact layout */
    @media (min-width: 768px) {
        .notification-actions { justify-content: flex-end; }
    }

    .new-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
        color: white;
        padding: 0.375rem 0.875rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 12px rgba(37, 176, 3, 0.3);
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    .new-badge i {
        font-size: 0.625rem;
        animation: sparkle 1.5s ease-in-out infinite;
    }

    @keyframes sparkle {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(0.8); }
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 5rem 2rem;
        color: var(--text-muted);
    }

    .empty-icon {
        font-size: 5rem;
        color: var(--border-color);
        margin-bottom: 1.5rem;
        opacity: 0.5;
    }

    .empty-state h5 {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.75rem;
    }

    .empty-state p {
        font-size: 1rem;
        color: var(--text-muted);
    }

    /* Pagination */
    .pagination-wrapper {
        background: var(--light-gray);
        padding: 1.5rem;
        border-top: 1px solid var(--border-color);
    }

    .pagination-info {
        font-size: 0.875rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    .load-more-panel {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .load-more-summary {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .load-more-btn {
        background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 999px;
        font-size: 0.9rem;
        font-weight: 700;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 8px 18px rgba(37, 176, 3, 0.2);
    }

    .load-more-btn:hover {
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(37, 176, 3, 0.28);
    }

    .load-more-status {
        font-size: 0.875rem;
        color: var(--text-muted);
        font-weight: 600;
    }

    /* Modal */
    .modal-content {
        border-radius: var(--border-radius);
        border: none;
        box-shadow: var(--shadow-lg);
    }

    .modal-header {
        background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
        color: white;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
        border: none;
        padding: 1.5rem;
    }

    .modal-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 600;
    }

    .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.8;
    }

    .btn-close:hover {
        opacity: 1;
    }

    .modal-body {
        padding: 2rem;
    }

    .alert-info {
        background: var(--success-light);
        border: 2px solid rgba(37, 176, 3, 0.2);
        border-radius: 0.5rem;
        padding: 1rem;
        margin-top: 1rem;
    }

    .modal-footer {
        border-top: 1px solid var(--border-color);
        padding: 1.5rem;
    }

    .modal-footer .btn-primary {
        background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
        border: none;
        padding: 0.75rem 1.75rem;
        border-radius: 2rem;
        font-weight: 600;
    }

    .modal-footer .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(37, 176, 3, 0.4);
    }

    .modal-footer .btn-secondary {
        background: white;
        border: 2px solid var(--border-color);
        color: var(--text-muted);
        padding: 0.75rem 1.75rem;
        border-radius: 2rem;
        font-weight: 600;
    }

    .modal-footer .btn-secondary:hover {
        background: var(--light-gray);
        border-color: var(--text-muted);
    }

    /* Success Toast */
    .success-toast {
        position: fixed;
        top: 2rem;
        right: 2rem;
        background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
        color: white;
        padding: 1.25rem 1.75rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-lg);
        z-index: 1050;
        display: flex;
        align-items: center;
        gap: 1rem;
        font-weight: 600;
        transform: translateX(400px);
        transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    .success-toast.show {
        transform: translateX(0);
    }

    .success-toast i {
        font-size: 1.5rem;
    }

    /* Responsive Design */
    @media (max-width: 992px) {
        .notification-content {
            gap: 1rem;
        }
        
        .notification-icon {
            width: 40px;
            height: 40px;
            min-width: 40px;
            font-size: 1rem;
        }
    }

    @media (max-width: 768px) {
        .notifications-container {
            padding: 1rem 0.5rem;
        }

        .page-header {
            padding: 2rem 1.5rem;
        }

        .page-title {
            font-size: 1.5rem;
        }

        .header-stats {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .notification-item {
            padding: 1.25rem;
        }

        .notification-content {
            flex-direction: column;
        }

        .notification-actions {
            flex-direction: column;
            width: 100%;
        }

        .load-more-panel {
            flex-direction: column;
            align-items: stretch;
        }

        .btn-view,
        .btn-mark-read,
        .load-more-btn {
            width: 100%;
            justify-content: center;
        }

        .new-badge {
            position: static;
            display: inline-flex;
            margin-bottom: 0.75rem;
        }

        .success-toast {
            left: 1rem;
            right: 1rem;
            transform: translateY(-100px);
        }

        .success-toast.show {
            transform: translateY(0);
        }
    }

    @media (max-width: 576px) {
        .page-header {
            padding: 1.5rem 1rem;
            margin-left: -0.5rem;
            margin-right: -0.5rem;
            border-radius: 0;
        }

        .mark-all-btn {
            width: 100%;
            justify-content: center;
            margin-top: 1rem;
        }

        .modal-body {
            padding: 1.5rem;
        }
    }

    /* Loading State */
    .btn-loading {
        position: relative;
        pointer-events: none;
        opacity: 0.7;
    }

    .btn-loading::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        top: 50%;
        left: 50%;
        margin-left: -8px;
        margin-top: -8px;
        border: 2px solid currentColor;
        border-right-color: transparent;
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
@endsection

@section('main')
@php
    $showBuyerSidebar = auth()->check() && auth()->user()->isBuyer();
    $showSellerSidebar = auth()->check() && auth()->user()->isSeller();
    $hasSidebar = $showBuyerSidebar || $showSellerSidebar;
    $notificationsIndexUrl = request()->url();
    $markAllReadUrl = request()->routeIs('seller.*') && \Illuminate\Support\Facades\Route::has('seller.notifications.mark-all-read')
        ? route('seller.notifications.mark-all-read')
        : (request()->routeIs('buyer.*') && \Illuminate\Support\Facades\Route::has('buyer.notifications.mark-all-read')
            ? route('buyer.notifications.mark-all-read')
            : route('notifications.mark-all-read'));
@endphp
<div class="py-8">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
        <div class="grid grid-cols-12 gap-4">
            @if($hasSidebar)
                <div class="col-span-12 lg:col-span-3">
                    @if($showBuyerSidebar)
                        @include('buyer.partials.sidebar')
                    @elseif($showSellerSidebar)
                        @include('seller.partials.sidebar')
                    @endif
                </div>
            @endif
            <div class="{{ $hasSidebar ? 'col-span-12 lg:col-span-9' : 'col-span-12' }}">
    <div class="notifications-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="flex justify-between items-center">
                <h1 class="page-title">
                    <i class="fas fa-bell"></i>
                    Notifications
                </h1>
                @if(($unreadCount ?? 0) > 0)
                    <form method="POST" action="{{ $markAllReadUrl }}" class="inline-block mark-all-form">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition mark-all-btn">
                            <i class="fas fa-check-double mr-1"></i>
                            Mark All as Read
                        </button>
                    </form>
                @endif
            </div>
            @if(($unreadCount ?? 0) > 0)
                <div class="mt-2">
                    <small class="opacity-75" id="unreadNotificationsSummary" data-unread-count="{{ $unreadCount }}">
                        You have {{ $unreadCount }} unread notifications
                    </small>
                </div>
            @endif
        </div>

        <!-- Notifications Card -->
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm notifications-card">
            <div class="p-4 sm:p-5 p-0">
                @if($notifications->count() > 0)
                    <div class="notification-list" id="notificationList">
                        @include('notifications.partials.items', [
                            'notifications' => $notifications,
                            'notificationsIndexUrl' => $notificationsIndexUrl,
                        ])
                        @if(false)
                        @foreach($notifications as $notification)
                            <div class="notification-item {{ !$notification->is_read ? 'unread' : '' }}" id="notification-{{ $notification->id }}">
                                @if(!$notification->is_read)
                                    <div class="new-badge">
                                        <i class="fas fa-star mr-1"></i>New
                                    </div>
                                @endif
                                
                                <div class="notification-content">
                                    @php
                                        $route = \App\Services\NotificationRouteService::getRouteForNotification($notification, auth()->user());
                                        $linkText = \App\Services\NotificationRouteService::getLinkText($notification, auth()->user());
                                        $href = $route;
                                    @endphp
                                    @if($href && $href !== route('notifications.index'))
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
                                        <span class="text-slate-500">•</span>
                                        <span>{{ $notification->created_at->diffForHumans() }}</span>
                                    </div>

                                    <div class="notification-actions">
                                        @if($href && $href !== route('notifications.index'))
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
                        @endif
                    </div>
                    
                    <div class="pagination-wrapper" id="loadMoreWrapper">
                        <div class="load-more-panel">
                            <div class="load-more-summary">
                                <div class="pagination-info" id="notificationsProgressText">
                                    Showing {{ $notifications->lastItem() ?? 0 }} of {{ $notifications->total() }} notifications
                                </div>
                                <div class="load-more-status" id="loadMoreStatusText">
                                    @if($notifications->hasMorePages())
                                        {{ $notifications->total() - ($notifications->lastItem() ?? 0) }} more notifications available
                                    @else
                                        All notifications are loaded
                                    @endif
                                </div>
                            </div>
                            <button type="button"
                                    class="load-more-btn {{ $notifications->hasMorePages() ? '' : 'hidden' }}"
                                    id="loadMoreNotificationsBtn"
                                    data-next-page-url="{{ $notifications->nextPageUrl() }}">
                                <i class="fas fa-plus-circle"></i>
                                Load more notifications
                            </button>
                        </div>
                    </div>
                    @if(false)
                        @if($notifications->hasPages())
                            <div class="pagination-wrapper">
                                <div class="flex justify-between items-center">
                                    <div class="pagination-info">
                                        Showing {{ $notifications->firstItem() }} to {{ $notifications->lastItem() }} 
                                        of {{ $notifications->total() }} notifications
                                    </div>
                                    <div>
                                        {{ $notifications->links("pagination::tailwind") }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                @else
                    <div class="empty-state">
                        <i class="far fa-bell-slash empty-icon"></i>
                        <h5 class="mb-3">No notifications yet</h5>
                        <p class="text-slate-500">You're all caught up! New notifications will appear here.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Mark as Read Confirmation Modal -->
    <div class="modal" id="markReadModal" tabindex="-1" aria-labelledby="markReadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-xl">
                <form id="markReadForm" method="POST">
                    @csrf
                    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                        <h5 class="text-base font-semibold text-slate-900" id="markReadModalLabel">
                            <i class="fas fa-check-circle mr-2"></i>Mark as Read
                        </h5>
                        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-ui-dismiss="modal" aria-label="Close">&times;</button>
                    </div>
                    <div class="px-4 py-4">
                        <p class="mb-3">Are you sure you want to mark this notification as read?</p>
                        <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800">
                            <strong><i class="fas fa-info-circle mr-1"></i>Notification:</strong>
                            <p class="mb-0 mt-2" id="notificationDescription"></p>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
                        <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-slate-600 text-white hover:bg-slate-500" data-ui-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>Cancel
                        </button>
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500">
                            <i class="fas fa-check mr-1"></i>Mark as Read
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Toast Template -->
    <div id="successToast" class="success-toast" style="display: none;">
        <i class="fas fa-check-circle"></i>
        <span id="toastMessage">Notification marked as read successfully!</span>
    </div>

</div>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentNotificationId = null;
    let currentButton = null;
    let currentListItem = null;
    let unreadSummary = document.getElementById('unreadNotificationsSummary');
    let markAllForm = document.querySelector('.mark-all-form');
    const markReadModal = document.getElementById('markReadModal');
    const markReadForm = document.getElementById('markReadForm');
    const notificationDescription = document.getElementById('notificationDescription');
    const notificationList = document.getElementById('notificationList');
    const loadMoreButton = document.getElementById('loadMoreNotificationsBtn');
    const progressText = document.getElementById('notificationsProgressText');
    const loadMoreStatusText = document.getElementById('loadMoreStatusText');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const markReadBaseUrl = @json(rtrim($notificationsIndexUrl, '/'));
    let isLoadingMore = false;

    const openMarkReadModal = () => {
        if (!markReadModal) return;
        markReadModal.classList.add('is-open');
        document.body.classList.add('overflow-hidden');
        markReadModal.dispatchEvent(new Event('shown.bs.modal'));
    };

    const closeMarkReadModal = () => {
        if (!markReadModal) return;
        markReadModal.classList.remove('is-open');
        if (!document.querySelector('.modal.is-open, .tw-modal.is-open')) {
            document.body.classList.remove('overflow-hidden');
        }
        markReadModal.dispatchEvent(new Event('hidden.bs.modal'));
    };

    const removeMarkAllForm = () => {
        if (!markAllForm) return;

        const formToRemove = markAllForm;
        markAllForm = null;
        formToRemove.style.transform = 'scale(0)';
        formToRemove.style.opacity = '0';
        setTimeout(() => formToRemove.remove(), 300);
    };

    const removeUnreadSummary = () => {
        if (!unreadSummary) return;

        const summaryElement = unreadSummary;
        const summaryContainer = summaryElement.parentElement;
        unreadSummary = null;

        if (summaryContainer) {
            summaryContainer.style.transform = 'scale(0.98)';
            summaryContainer.style.opacity = '0';
            setTimeout(() => summaryContainer.remove(), 300);
            return;
        }

        summaryElement.remove();
    };

    const syncUnreadSummary = (count) => {
        const normalizedCount = Math.max(0, Number(count) || 0);

        if (unreadSummary) {
            unreadSummary.dataset.unreadCount = String(normalizedCount);
            unreadSummary.textContent = normalizedCount > 0
                ? `You have ${normalizedCount} unread notification${normalizedCount === 1 ? '' : 's'}`
                : 'You have no unread notifications';
        }

        if (normalizedCount === 0) {
            removeUnreadSummary();
            removeMarkAllForm();
        }
    };

    const decrementUnreadSummary = () => {
        if (!unreadSummary) {
            removeMarkAllForm();
            return;
        }

        const currentUnreadCount = Number(unreadSummary.dataset.unreadCount || '0');
        syncUnreadSummary(currentUnreadCount - 1);
    };

    const updateLoadMoreSummary = (shownCount, totalCount, hasMorePages, nextPageUrl = '') => {
        const normalizedShownCount = Math.max(0, Number(shownCount) || 0);
        const normalizedTotalCount = Math.max(0, Number(totalCount) || 0);
        const remainingCount = Math.max(normalizedTotalCount - normalizedShownCount, 0);

        if (progressText) {
            progressText.textContent = `Showing ${Math.min(normalizedShownCount, normalizedTotalCount)} of ${normalizedTotalCount} notifications`;
        }

        if (loadMoreStatusText) {
            loadMoreStatusText.textContent = hasMorePages
                ? `${remainingCount} more notification${remainingCount === 1 ? '' : 's'} available`
                : 'All notifications are loaded';
        }

        if (loadMoreButton) {
            loadMoreButton.dataset.nextPageUrl = nextPageUrl || '';
            loadMoreButton.classList.toggle('hidden', !hasMorePages || !nextPageUrl);
        }
    };

    const loadMoreNotifications = async (nextPageUrl) => {
        if (!nextPageUrl || !notificationList || !loadMoreButton || isLoadingMore) {
            return;
        }

        isLoadingMore = true;
        const originalButtonHtml = loadMoreButton.innerHTML;
        loadMoreButton.classList.add('btn-loading');
        loadMoreButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Loading...';
        loadMoreButton.disabled = true;

        try {
            const response = await fetch(nextPageUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`Unable to load more notifications (${response.status})`);
            }

            const payload = await response.json();

            if (payload.html) {
                notificationList.insertAdjacentHTML('beforeend', payload.html);
            }

            updateLoadMoreSummary(
                payload.shown_count ?? notificationList.querySelectorAll('.notification-item').length,
                payload.total_count ?? notificationList.querySelectorAll('.notification-item').length,
                Boolean(payload.has_more_pages),
                payload.next_page_url ?? ''
            );

            if (payload.unread_count !== undefined) {
                syncUnreadSummary(payload.unread_count);
            }
        } catch (error) {
            console.error('Error:', error);
            showSuccessToast('Error loading more notifications. Please try again.');
        } finally {
            isLoadingMore = false;
            loadMoreButton.classList.remove('btn-loading');
            loadMoreButton.innerHTML = originalButtonHtml;
            loadMoreButton.disabled = false;
        }
    };

    function showSuccessToast(message = 'Notification marked as read successfully!') {
        const toast = document.getElementById('successToast');
        const messageElement = document.getElementById('toastMessage');

        messageElement.textContent = message;
        toast.style.display = 'flex';

        setTimeout(() => toast.classList.add('show'), 100);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.style.display = 'none', 400);
        }, 4000);
    }

    document.addEventListener('click', function(e) {
        const markReadButton = e.target.closest('.btn-mark-read');
        if (markReadButton) {
            e.preventDefault();

            currentNotificationId = markReadButton.dataset.notificationId;
            currentButton = markReadButton;
            currentListItem = document.getElementById(`notification-${currentNotificationId}`);

            if (notificationDescription) {
                notificationDescription.textContent = markReadButton.dataset.notificationDescription;
            }
            if (markReadForm) {
                markReadForm.action = `${markReadBaseUrl}/${currentNotificationId}/mark-read`;
            }

            openMarkReadModal();
            return;
        }

        const loadMoreTrigger = e.target.closest('#loadMoreNotificationsBtn');
        if (loadMoreTrigger) {
            e.preventDefault();
            loadMoreNotifications(loadMoreTrigger.dataset.nextPageUrl);
            return;
        }

        const dismissTrigger = e.target.closest('[data-ui-dismiss="modal"]');
        if (dismissTrigger && markReadModal?.classList.contains('is-open')) {
            e.preventDefault();
            closeMarkReadModal();
        }
    });

    markReadModal?.addEventListener('click', function(e) {
        if (e.target === markReadModal) {
            closeMarkReadModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && markReadModal?.classList.contains('is-open')) {
            closeMarkReadModal();
        }
    });

    markReadForm?.addEventListener('submit', function(e) {
        e.preventDefault();

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalHTML = submitBtn.innerHTML;
        submitBtn.classList.add('btn-loading');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Processing...';
        submitBtn.disabled = true;

        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Unable to mark notification as read (${response.status})`);
            }

            return response.json();
        })
        .then(data => {
            if (data.success) {
                const badge = currentListItem?.querySelector('.new-badge');
                if (badge) {
                    badge.style.transform = 'scale(0)';
                    badge.style.opacity = '0';
                    setTimeout(() => badge.remove(), 300);
                }

                if (currentButton) {
                    currentButton.style.transform = 'scale(0)';
                    currentButton.style.opacity = '0';
                    setTimeout(() => currentButton.remove(), 300);
                }

                currentListItem?.querySelector('.notification-text')?.classList.remove('unread');
                currentListItem?.querySelector('.notification-icon')?.classList.remove('unread');
                currentListItem?.classList.remove('unread');

                closeMarkReadModal();
                showSuccessToast();
                decrementUnreadSummary();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showSuccessToast('Error marking notification as read. Please try again.');
        })
        .finally(() => {
            submitBtn.classList.remove('btn-loading');
            submitBtn.innerHTML = originalHTML;
            submitBtn.disabled = false;
        });
    });

    if (markAllForm) {
        markAllForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalHTML = submitBtn.innerHTML;
            submitBtn.classList.add('btn-loading');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Processing...';
            submitBtn.disabled = true;

            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Unable to mark all notifications as read (${response.status})`);
                }

                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showSuccessToast('All notifications marked as read!');
                    setTimeout(() => location.reload(), 1000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.classList.remove('btn-loading');
                submitBtn.innerHTML = originalHTML;
                submitBtn.disabled = false;
                showSuccessToast('Error marking all notifications as read. Please try again.');
            });
        });
    }

    markReadModal?.addEventListener('hidden.bs.modal', function() {
        currentNotificationId = null;
        currentButton = null;
        currentListItem = null;
    });
});
</script>
@endpush
@endsection
