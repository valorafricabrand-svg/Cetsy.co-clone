@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="content ">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Notifications</h5>
                    @if($notifications->where('is_read', false)->count() > 0)
                        <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                Mark All as Read
                            </button>
                        </form>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($notifications->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($notifications as $notification)
                                <div class="list-group-item d-flex align-items-start p-3 {{ !$notification->is_read ? 'bg-light' : '' }}">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <p class="mb-1 {{ !$notification->is_read ? 'fw-bold' : '' }}">
                                                    {{ $notification->description }}
                                                </p>
                                                <small class="text-muted">
                                                    {{ $notification->created_at->format('M d, Y \a\t g:i A') }}
                                                    ({{ $notification->created_at->diffForHumans() }})
                                                </small>
                                                @php
                                                    $route = \App\Services\NotificationRouteService::getRouteForNotification($notification, auth()->user());
                                                    $linkText = \App\Services\NotificationRouteService::getLinkText($notification, auth()->user());
                                                @endphp
                                                @if($route && $route !== route('notifications.index'))
                                                    <div class="mt-2">
                                                        <a href="{{ $route }}" class="btn btn-sm btn-outline-primary">
                                                            {{ $linkText }}
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ms-3">
                                                @if(!$notification->is_read)
                                                    <span class="badge bg-primary rounded-pill">New</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @if(!$notification->is_read)
                                        <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}" class="ms-2">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Mark Read</button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    Showing {{ $notifications->firstItem() }} to {{ $notifications->lastItem() }} 
                                    of {{ $notifications->total() }} notifications
                                </div>
                                <div>
                                    {{ $notifications->links() }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted">No notifications found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mark as Read Confirmation Modal -->
<div class="modal fade" id="markReadModal" tabindex="-1" aria-labelledby="markReadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('notifications.mark-read', $notification->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="markReadModalLabel">Confirm Mark as Read</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to mark this notification as read?</p>
                    <div class="alert alert-info">
                        <strong>Notification:</strong>
                        <p class="mb-0 mt-2" id="notificationDescription"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Mark as Read</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentNotificationId = null;
    let currentButton = null;
    let currentListItem = null;
    
    // Handle mark as read buttons - open modal
    document.querySelectorAll('.mark-read-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const notificationId = this.dataset.notificationId;
            const notificationDescription = this.dataset.notificationDescription;
            
            // Store current elements for later use
            currentNotificationId = notificationId;
            currentButton = this;
            currentListItem = this.closest('.list-group-item');
            
            // Update modal content
            document.getElementById('notificationDescription').textContent = notificationDescription;
            
            // Update form action
            const form = document.getElementById('markReadForm');
            form.action = `/notifications/${notificationId}/mark-read`;
        });
    });
    
    // Handle form submission
    document.getElementById('markReadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!currentNotificationId) {
            console.error('No notification ID found');
            return;
        }
        
        console.log('Marking notification as read:', currentNotificationId);
        
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
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                // Remove the "New" badge
                const badge = currentListItem.querySelector('.badge');
                if (badge) {
                    badge.remove();
                }
                
                // Remove the mark as read button
                currentButton.remove();
                
                // Remove the bold styling
                const description = currentListItem.querySelector('p');
                if (description) {
                    description.classList.remove('fw-bold');
                }
                
                // Remove the background highlight
                currentListItem.classList.remove('bg-light');
                
                // Close the modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('markReadModal'));
                if (modal) {
                    modal.hide();
                }
                
                // Show success message
                alert('Notification marked as read successfully!');
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
            alert('Error marking notification as read. Please try again.');
        });
    });
    
    // Reset variables when modal is hidden
    document.getElementById('markReadModal').addEventListener('hidden.bs.modal', function() {
        currentNotificationId = null;
        currentButton = null;
        currentListItem = null;
    });
});
</script>
@endpush
@endsection 