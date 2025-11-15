
@extends('layouts.app')

@section('styles')
<style>
    :root {
        --primary-color: #3bca50;
        --secondary-color: #05f7a6;
        --success-color: #14a44d;
        --unread-bg: rgba(236, 245, 241, 0.5);
        --border-color: #e9ecef;
        --text-muted: #6c757d;
        --shadow-sm: 0 .125rem .25rem rgba(0,0,0,.075);
        --shadow-md: 0 .5rem 1rem rgba(0,0,0,.08);
        --transition: all 0.2s ease-in-out;
    }
    
    .page-notifications {
        padding-bottom: 1.5rem;
    }
    
    .notification-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #212529;
        margin-bottom: 0.25rem;
    }
    
    .notification-item {
        border-left: 3px solid transparent;
        transition: var(--transition);
        margin-bottom: 0.5rem;
        border-radius: 0.375rem;
        text-decoration: none;
        color: inherit;
    }
    
    .notification-item:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
        z-index: 1;
        position: relative;
    }
    
    .notification-item.unread {
        border-left-color: var(--primary-color);
        background-color: var(--unread-bg) !important;
    }
    
    .notification-badge {
        background-color: var(--primary-color);
        font-size: 0.7rem;
        font-weight: 500;
        padding: 0.25rem 0.5rem;
    }
    
    .notification-time {
        font-size: 0.8rem;
        color: var(--text-muted);
        white-space: nowrap;
    }
    
    .notification-message {
        color: #495057;
        font-size: 0.95rem;
        line-height: 1.5;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    
    .btn-mark-read {
        color: var(--secondary-color);
        border-color: var(--border-color);
        font-size: 0.85rem;
        padding: 0.25rem 0.75rem;
        transition: var(--transition);
    }
    
    .btn-mark-read:hover {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }
    
    .btn-mark-all {
        background-color: #f8f9fa;
        color: #212529;
        border: 1px solid var(--border-color);
        font-weight: 500;
        padding: 0.5rem 1rem;
        transition: var(--transition);
        border-radius: 0.375rem;
    }
    
    .btn-mark-all:hover {
        background-color: var(--border-color);
    }
    
    .notification-card {
        border: none;
        box-shadow: var(--shadow-sm);
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .notification-header {
        background-color: #ffffff;
        border-bottom: 1px solid var(--border-color);
        padding: 1rem 1.25rem;
    }
    
    .empty-state {
        padding: 3rem 1.5rem;
    }
    
    .empty-state-icon {
        color: #dee2e6;
        font-size: 3.5rem;
    }
    
    .notification-timestamp {
        font-size: 0.85rem;
        color: var(--text-muted);
    }
    
    .user-info {
        background-color: #f8f9fa;
        border-radius: 0.375rem;
        padding: 0.75rem 1rem;
        margin-bottom: 1.5rem;
        border-left: 3px solid var(--primary-color);
        display: flex;
        align-items: center;
    }
    
    .user-info .avatar {
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        background-color: var(--primary-color);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-right: 0.75rem;
    }
    
    .status-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 0.5rem;
    }
    
    .status-indicator.unread {
        background-color: var(--primary-color);
    }
    
    .status-indicator.read {
        background-color: #dee2e6;
    }
    
    @media (max-width: 767.98px) {
        .notification-header {
            padding: 0.75rem 1rem;
        }
        
        .notification-item {
            padding: 0.75rem !important;
        }
        
        .btn-mark-all {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
        }
        
        .notification-title {
            font-size: 1.25rem;
        }
    }
    
    .page-header {
        position: sticky;
        top: 0;
        background-color: #f8f9fa;
        z-index: 1020;
        padding: 1rem 0;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid var(--border-color);
    }
    
    .pagination {
        justify-content: center;
    }
    
    /* Loading state */
    .placeholder-glow .placeholder {
        background-color: #e9ecef;
    }
</style>
@endsection

@section('content')
<div class="content">
<div class="page-notifications">
    <!-- Sticky Header -->
    <div class="page-header mb-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="notification-title">Notifications Dashboard</h1>
                    <p class="notification-timestamp mb-0 d-flex align-items-center">
                    </button>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-mark-all">
                            <i class="fas fa-check-double me-1"></i> Mark All as Read
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Success Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Stats Row -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 p-3 rounded me-3">
                                <i class="fas fa-bell text-primary"></i>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-1 text-muted">Total Notifications</h6>
                                <h3 class="card-title mb-0">{{ $notifications->total() ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-danger bg-opacity-10 p-3 rounded me-3">
                                <i class="fas fa-bell-slash text-danger"></i>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-1 text-muted">Unread</h6>
                                <h3 class="card-title mb-0">{{ $notifications->where('is_read', false)->count() ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 p-3 rounded me-3">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-1 text-muted">Read</h6>
                                <h3 class="card-title mb-0">{{ $notifications->where('is_read', true)->count() ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Notification Card -->
        <div class="card notification-card mb-4">
            <div class="notification-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-bell me-2 text-primary"></i> 
                    <strong>System Notifications</strong>
                </div>
                <div>
                    <span class="badge bg-secondary rounded-pill">{{ $notifications->total() ?? 0 }}</span>
                </div>
            </div>
            <div class="card-body p-0">
                @if($notifications->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($notifications as $notification)
                            @php
                                $route = $notification->link ?? \App\Services\NotificationRouteService::getRouteForNotification($notification, auth()->user());
                            @endphp
                            <a href="{{ $route ?? '#' }}" class="list-group-item list-group-item-action notification-item {{ !$notification->is_read ? 'unread' : '' }} p-3">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h5 class="mb-1 fw-bold d-flex align-items-center">
                                        <span class="status-indicator {{ !$notification->is_read ? 'unread' : 'read' }}"></span>
                                        @if(!$notification->is_read)
                                            <span class="badge notification-badge rounded-pill me-2">New</span>
                                        @endif
                                        @if(!empty($notification->type))
                                            <span class="badge rounded-pill ms-1 {{ $notification->type === \App\Models\Activity::TYPE_KYC ? 'bg-info text-dark' : 'bg-light text-dark' }}">
                                                {{ strtoupper($notification->type) }}
                                            </span>
                                        @endif
                                    </h5>
                                    <small class="notification-time">
                                        <i class="far fa-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <p class="notification-message mb-2">{{ $notification->description }}</p>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        @if(isset($notification->causer_id))
                                            @php
                                                $displayName = 'System';
                                                $causer = $notification->causer;
                                                if ($causer) {
                                                    // If causer is a User and a seller, prefer their shop name
                                                    if ($causer instanceof \App\Models\User) {
                                                        if ($causer->isSeller() && optional($causer->shop)->name) {
                                                            $displayName = $causer->shop->name;
                                                        } else {
                                                            $displayName = $causer->name ?? 'System';
                                                        }
                                                    } elseif (isset($causer->name)) {
                                                        $displayName = $causer->name;
                                                    }
                                                }
                                            @endphp
                                            <i class="fas fa-user me-1"></i> By: {{ $displayName }}
                                        @endif
                                    </small>
                                    
                                    @if(!$notification->is_read)
                                        <form action="{{ route('admin.notifications.mark-read', $notification->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-mark-read">
                                                <i class="fas fa-check me-1"></i> Mark as Read
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    <div class="p-3 border-top">
                        <div class="d-flex justify-content-center">
                            {{ $notifications->links("pagination::bootstrap-5") }}
                        </div>
                    </div>
                @else
                    <div class="empty-state text-center">
                        <div class="empty-state-icon mb-3">
                            <i class="far fa-bell-slash"></i>
                        </div>
                        <h5 class="fw-light text-secondary mb-1">No Notifications</h5>
                        <p class="text-muted mb-3">You don't have any notifications at the moment.</p>
                        <button class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()">
                            <i class="fas fa-sync me-1"></i> Refresh
                        </button>
                    </div>
                @endif
            </div>
        </div>
    
        <!-- Loading State (Example) -->
        <div class="d-none">
            <div class="card notification-card mb-4">
                <div class="notification-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-bell me-2"></i> 
                        <strong>System Notifications</strong>
                    </div>
                    <div>
                        <span class="placeholder col-2"></span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush placeholder-glow">
                        @for($i = 0; $i < 3; $i++)
                            <div class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h5 class="mb-1 fw-bold">
                                        <span class="placeholder col-4"></span>
                                    </h5>
                                    <small>
                                        <span class="placeholder col-4"></span>
                                    </small>
                                </div>
                                <p class="mb-2">
                                    <span class="placeholder col-7"></span>
                                    <span class="placeholder col-4"></span>
                                </p>
                                <div class="d-flex justify-content-end">
                                    <span class="placeholder col-2"></span>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@push('scripts')
<script>
    // Optional JavaScript for enhanced interactivity
    document.addEventListener('DOMContentLoaded', function() {
        const notificationItems = document.querySelectorAll('.notification-item');
        
        notificationItems.forEach(item => {
            // Add hover effect manually if needed
            item.addEventListener('mouseenter', function() {
                this.style.backgroundColor = this.classList.contains('unread') 
                    ? 'rgba(236, 240, 245, 0.7)' 
                    : 'rgba(248, 249, 250, 0.7)';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.backgroundColor = this.classList.contains('unread')
                    ? 'rgba(236, 240, 245, 0.5)'
                    : '';
            });
        });
    });
</script>
@endpush
@endsection
