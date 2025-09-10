@extends('layouts.app')

@section('title', 'Notifications')

@section('styles')
<style>
    :root {
        --primary-color: #4361ee;
        --success-color: #28a745;
        --info-color: #17a2b8;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --light-gray: #f8f9fa;
        --border-color: #e9ecef;
        --text-muted: #6c757d;
        --shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        --border-radius: 10px;
        --transition: all 0.3s ease;
    }

    .notifications-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .page-header {
        background: linear-gradient(135deg, var(--primary-color), #5a67d8);
        color: white;
        padding: 2rem;
        border-radius: var(--border-radius);
        margin-bottom: 2rem;
        box-shadow: var(--shadow);
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .page-title i {
        font-size: 1.5rem;
        opacity: 0.9;
    }

    .notifications-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        border: none;
    }

    .card-header {
        background: white;
        border-bottom: 2px solid var(--border-color);
        padding: 1.5rem;
    }

    .mark-all-btn {
        background: linear-gradient(135deg, var(--info-color), #20c997);
        border: none;
        color: white;
        padding: 0.5rem 1.25rem;
        border-radius: 25px;
        font-weight: 500;
        transition: var(--transition);
        box-shadow: 0 2px 8px rgba(23, 162, 184, 0.3);
    }

    .mark-all-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(23, 162, 184, 0.4);
        color: white;
    }

    .notification-item {
        border: none;
        padding: 1.5rem;
        transition: var(--transition);
        border-bottom: 1px solid var(--border-color);
        position: relative;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-item.unread {
        background: linear-gradient(135deg, #f8f9ff, #fff5f5);
        border-left: 4px solid var(--primary-color);
    }

    .notification-item:hover {
        background: var(--light-gray);
        transform: translateX(5px);
    }

    .notification-content {
        flex-grow: 1;
    }

    .notification-text {
        font-size: 1rem;
        line-height: 1.5;
        margin-bottom: 0.75rem;
        color: #2d3748;
    }

    .notification-text.unread {
        font-weight: 600;
        color: #1a202c;
    }

    .notification-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        font-size: 0.875rem;
        color: var(--text-muted);
        margin-bottom: 0.75rem;
    }

    .notification-date {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .notification-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .btn-view {
        background: linear-gradient(135deg, var(--primary-color), #5a67d8);
        border: none;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
        transition: var(--transition);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-view:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        color: white;
        text-decoration: none;
    }

    .btn-mark-read {
        background: white;
        border: 2px solid var(--border-color);
        color: var(--text-muted);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
        transition: var(--transition);
        cursor: pointer;
    }

    .btn-mark-read:hover {
        border-color: var(--success-color);
        color: var(--success-color);
        transform: translateY(-1px);
    }

    .new-badge {
        background: linear-gradient(135deg, var(--primary-color), #667eea);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
        position: absolute;
        top: 1rem;
        right: 1rem;
        box-shadow: 0 2px 8px rgba(67, 97, 238, 0.3);
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-muted);
    }

    .empty-icon {
        font-size: 4rem;
        color: var(--border-color);
        margin-bottom: 1rem;
    }

    .pagination-wrapper {
        background: white;
        padding: 1.5rem;
        border-top: 1px solid var(--border-color);
        border-radius: 0 0 var(--border-radius) var(--border-radius);
    }

    .pagination-info {
        font-size: 0.875rem;
        color: var(--text-muted);
    }

    /* Success Toast Styling */
    .success-toast {
        position: fixed;
        top: 2rem;
        right: 2rem;
        background: linear-gradient(135deg, var(--success-color), #20c997);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: var(--border-radius);
        box-shadow: 0 4px 20px rgba(40, 167, 69, 0.3);
        z-index: 1050;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 500;
        transform: translateX(400px);
        transition: transform 0.3s ease;
    }

    .success-toast.show {
        transform: translateX(0);
    }

    .success-toast i {
        font-size: 1.25rem;
    }

    /* Modal Styling */
    .modal-content {
        border-radius: var(--border-radius);
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        background: linear-gradient(135deg, var(--primary-color), #5a67d8);
        color: white;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
    }

    .btn-close {
        filter: invert(1);
    }

    .alert-info {
        background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
        border: none;
        border-left: 4px solid var(--info-color);
        border-radius: 8px;
    }

    .modal-footer .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), #5a67d8);
        border: none;
        padding: 0.5rem 1.5rem;
        border-radius: 20px;
    }

    .modal-footer .btn-secondary {
        background: white;
        border: 2px solid var(--border-color);
        color: var(--text-muted);
        padding: 0.5rem 1.5rem;
        border-radius: 20px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .notifications-container {
            padding: 1rem 0.5rem;
        }

        .notification-item {
            padding: 1rem;
        }

        .notification-actions {
            flex-direction: column;
        }

        .success-toast {
            right: 1rem;
            left: 1rem;
            transform: translateY(-100px);
        }

        .success-toast.show {
            transform: translateY(0);
        }
    }
</style>
@endSection

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
                <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="d-inline">
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
                                <p class="notification-text {{ !$notification->is_read ? 'unread' : '' }}">
                                    {{ $notification->description }}
                                </p>
                                
                                <div class="notification-meta">
                                    <div class="notification-date">
                                        <i class="far fa-clock"></i>
                                        {{ $notification->created_at->format('M d, Y \a\t g:i A') }}
                                    </div>
                                    <span class="text-muted">•</span>
                                    <span>{{ $notification->created_at->diffForHumans() }}</span>
                                </div>

                                <div class="notification-actions">
                                    @php
                                        $route = \App\Services\NotificationRouteService::getRouteForNotification($notification, auth()->user());
                                        $linkText = \App\Services\NotificationRouteService::getLinkText($notification, auth()->user());
                                    @endphp
                                    @if($route && $route !== route('notifications.index'))
                                        <a href="{{ $route }}" class="btn-view">
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
                                {{ $notifications->links() }}
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
</div>

<!-- Success Toast Template -->
<div id="successToast" class="success-toast" style="display: none;">
    <i class="fas fa-check-circle"></i>
    <span id="toastMessage">Notification marked as read successfully!</span>
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
        
        // Trigger animation
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        // Auto hide after 4 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.style.display = 'none';
            }, 300);
        }, 4000);
    }
    
    // Handle mark as read buttons
    document.querySelectorAll('.btn-mark-read').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const notificationId = this.dataset.notificationId;
            const notificationDescription = this.dataset.notificationDescription;
            
            // Store current elements for later use
            currentNotificationId = notificationId;
            currentButton = this;
            currentListItem = document.getElementById(`notification-${notificationId}`);
            
            // Update modal content
            document.getElementById('notificationDescription').textContent = notificationDescription;
            
            // Update form action
            const form = document.getElementById('markReadForm');
            form.action = `/notifications/${notificationId}/mark-read`;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('markReadModal'));
            modal.show();
        });
    });
    
    // Handle form submission
    document.getElementById('markReadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!currentNotificationId) {
            console.error('No notification ID found');
            return;
        }
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
        submitBtn.disabled = true;
        
        // Submit the form
        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Remove the "New" badge
                const badge = currentListItem.querySelector('.new-badge');
                if (badge) {
                    badge.style.transform = 'scale(0)';
                    setTimeout(() => badge.remove(), 200);
                }
                
                // Remove the mark as read button
                currentButton.style.transform = 'scale(0)';
                setTimeout(() => currentButton.remove(), 200);
                
                // Remove the bold styling
                const description = currentListItem.querySelector('.notification-text');
                if (description) {
                    description.classList.remove('unread');
                }
                
                // Remove the unread styling
                currentListItem.classList.remove('unread');
                
                // Close the modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('markReadModal'));
                if (modal) {
                    modal.hide();
                }
                
                // Show success toast
                showSuccessToast();
                
                // Update page title if needed (optional)
                const unreadCount = document.querySelectorAll('.notification-item.unread').length;
                if (unreadCount === 0) {
                    const markAllBtn = document.querySelector('.mark-all-btn');
                    if (markAllBtn) {
                        markAllBtn.style.transform = 'scale(0)';
                        setTimeout(() => markAllBtn.remove(), 200);
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
            showSuccessToast('Error marking notification as read. Please try again.');
            
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        })
        .finally(() => {
            // Reset button state if still visible
            if (submitBtn && submitBtn.parentNode) {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    });
    
    // Reset variables when modal is hidden
    document.getElementById('markReadModal').addEventListener('hidden.bs.modal', function() {
        currentNotificationId = null;
        currentButton = null;
        currentListItem = null;
    });
    
    // Handle mark all as read form
    const markAllForm = document.querySelector('form[action*="mark-all-read"]');
    if (markAllForm) {
        markAllForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
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
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Refresh page to show updated notifications
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error marking all notifications as read:', error);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                showSuccessToast('Error marking all notifications as read. Please try again.');
            });
        });
    }
});
</script>
@endpush
@endsection