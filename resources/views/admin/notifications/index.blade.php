@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="content">
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Notifications</h2>
        <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Notification
        </a>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($notifications->count())
        <ul class="list-group">
            @foreach($notifications as $notification)
                <li class="list-group-item d-flex align-items-center">
                    @if($notification->icon)
                        <i class="{{ $notification->icon }} fa-lg me-3 text-primary"></i>
                    @else
                        <i class="fas fa-bell fa-lg me-3 text-primary"></i>
                    @endif
                    <div class="flex-grow-1">
                        <strong>{{ $notification->title }}</strong>
                        <div>{{ $notification->message }}</div>
                        @if($notification->link)
                            <a href="{{ $notification->link }}" class="small text-info" target="_blank">View More</a>
                        @endif
                        <div>
                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            @if(!$notification->is_read)
                                <span class="badge bg-warning ms-2">Unread</span>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
        <div class="mt-3">
            {{ $notifications->links() }}
        </div>
    @else
        <div class="alert alert-info mt-4">No notifications found.</div>
    @endif
</div>
</div>
@endsection