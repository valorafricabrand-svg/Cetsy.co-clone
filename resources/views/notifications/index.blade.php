@extends('layouts.app')

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

        .btn-view,
        .btn-mark-read {
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

@section('content')
<div class="content">
    <div class="notifications-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="page-title">
                    <i class="fas fa-bell"></i>
                    Notifications
                </h1>
                @if($notifications->where('is_read', false)->count() > 0)
                    <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="d-inline mark-all-form">
                        @csrf
                        <button type="submit" class="btn mark-all-btn">
                            <i class="fas fa-check-double me-1"></i>
                            Mark All as Read
                        </button>
                    </form>
                @endif
            </div>
            @if($notifications->where('is_read', false)->count() > 0)
                <div class="mt-2">
                    <small class="opacity-75">
                        You have {{ $notifications->where('is_read', false)->count() }} unread notifications
                    </small>
                </div>
            @endif
        </div>

        <!-- Notifications Card -->
        <div class="card notifications-card">
            <div class="card-body p-0">
                @if($notifications->count() > 0)
                    <div class="notification-list">
                        @foreach($notifications as $notification)
                            <div class="notification-item {{ !$notification->is_read ? 'unread' : '' }}" id="notification-{{ $notification->id }}">
                                @if(!$notification->is_read)
                                    <div class="new-badge">
                                        <i class="fas fa-star me-1"></i>New
                                    </div>
                                @endif
                                
                                <div class="notification-content">
                                    @php
                                        $route = \App\Services\NotificationRouteService::getRouteForNotification($notification, auth()->user());
                                        $linkText = \App\Services\NotificationRouteService::getLinkText($notification, auth()->user());
                                        $hasOpen = \Illuminate\Support\Facades\Route::has('notifications.open');
                                        $href = $hasOpen ? route('notifications.open', $notification->id) : $route;
                                    @endphp
                                    @if($route && $route !== route('notifications.index'))
                                        <a href="{{ $href }}" class="notification-text {{ !$notification->is_read ? 'unread' : '' }} text-decoration-none d-block">
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
                                        <span class="text-muted">•</span>
                                        <span>{{ $notification->created_at->diffForHumans() }}</span>
                                    </div>

                                    <div class="notification-actions">
                                        @if($route && $route !== route('notifications.index'))
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
                                                <i class="fas fa-check me-1"></i>Mark as Read
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    @if($notifications->hasPages())
                        <div class="pagination-wrapper">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="pagination-info">
                                    Showing {{ $notifications->firstItem() }} to {{ $notifications->lastItem() }} 
                                    of {{ $notifications->total() }} notifications
                                </div>
                                <div>
                                    {{ $notifications->links("pagination::bootstrap-5") }}
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="empty-state">
                        <i class="far fa-bell-slash empty-icon"></i>
                        <h5 class="mb-3">No notifications yet</h5>
                        <p class="text-muted">You're all caught up! New notifications will appear here.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Mark as Read Confirmation Modal -->
    <div class="modal fade" id="markReadModal" tabindex="-1" aria-labelledby="markReadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="markReadForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="markReadModalLabel">
                            <i class="fas fa-check-circle me-2"></i>Mark as Read
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Are you sure you want to mark this notification as read?</p>
                        <div class="alert alert-info">
                            <strong><i class="fas fa-info-circle me-1"></i>Notification:</strong>
                            <p class="mb-0 mt-2" id="notificationDescription"></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check me-1"></i>Mark as Read
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


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentNotificationId = null;
    let currentButton = null;
    let currentListItem = null;
    
    // Success toast function
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
    
    // Handle mark as read buttons
    document.querySelectorAll('.btn-mark-read').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            currentNotificationId = this.dataset.notificationId;
            currentButton = this;
            currentListItem = document.getElementById(`notification-${currentNotificationId}`);
            
            document.getElementById('notificationDescription').textContent = this.dataset.notificationDescription;
            document.getElementById('markReadForm').action = `{{ url('/notifications') }}/${currentNotificationId}/mark-read`;
            
            const modal = new bootstrap.Modal(document.getElementById('markReadModal'));
            modal.show();
        });
    });
    
    // Handle form submission
    document.getElementById('markReadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalHTML = submitBtn.innerHTML;
        submitBtn.classList.add('btn-loading');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
        submitBtn.disabled = true;
        
        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Animate and remove elements
                const badge = currentListItem.querySelector('.new-badge');
                if (badge) {
                    badge.style.transform = 'scale(0)';
                    badge.style.opacity = '0';
                    setTimeout(() => badge.remove(), 300);
                }
                
                currentButton.style.transform = 'scale(0)';
                currentButton.style.opacity = '0';
                setTimeout(() => currentButton.remove(), 300);
                
                currentListItem.querySelector('.notification-text')?.classList.remove('unread');
                currentListItem.querySelector('.notification-icon')?.classList.remove('unread');
                currentListItem.classList.remove('unread');
                
                const modal = bootstrap.Modal.getInstance(document.getElementById('markReadModal'));
                modal?.hide();
                
                showSuccessToast();
                
                // Update stats
                const unreadCount = document.querySelectorAll('.notification-item.unread').length;
                if (unreadCount === 0) {
                    const markAllBtn = document.querySelector('.mark-all-form');
                    if (markAllBtn) {
                        markAllBtn.style.transform = 'scale(0)';
                        markAllBtn.style.opacity = '0';
                        setTimeout(() => markAllBtn.remove(), 300);
                    }
                }
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
    
    // Handle mark all as read
    const markAllForm = document.querySelector('.mark-all-form');
    if (markAllForm) {
        markAllForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalHTML = submitBtn.innerHTML;
            submitBtn.classList.add('btn-loading');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
            submitBtn.disabled = true;
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
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
    
    // Reset modal state
    document.getElementById('markReadModal').addEventListener('hidden.bs.modal', function() {
        currentNotificationId = null;
        currentButton = null;
        currentListItem = null;
    });
});
</script>
@endpush
@endsection
