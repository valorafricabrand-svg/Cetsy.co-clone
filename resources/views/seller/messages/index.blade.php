@extends('layouts.app')
@section('title', 'Customer Conversations')

@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">Customer Conversations</h1>
            <p class="text-muted mb-0">View and reply to conversations with customers about your products</p>
            @php
                $unreadCount = $conversations->sum('unread_count');
                $totalCount = $conversations->count();
            @endphp
            @if($unreadCount > 0)
                <div class="mt-2">
                    <span class="badge bg-warning text-dark">
                        <i class="bi bi-circle-fill me-1"></i>{{ $unreadCount }} new message{{ $unreadCount > 1 ? 's' : '' }}
                    </span>
                    <span class="text-muted small ms-2">in {{ $totalCount }} conversation{{ $totalCount > 1 ? 's' : '' }}</span>
                </div>
            @endif
        </div>
        @if($unreadCount > 0)
            <div>
                <form method="POST" action="{{ route('seller.messages.bulk-mark-read') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Mark all unread messages as read?')">
                        <i class="bi bi-check-circle me-1"></i>Mark All as Read
                    </button>
                </form>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    {{-- Filter Section --}}
    <div class="card shadow border-0 mb-3">
        <div class="card-body p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center">
                    <i class="bi bi-funnel me-2 text-muted"></i>
                    <span class="text-muted small">Filter:</span>
                </div>
                <div class="btn-group" role="group">
                    <a href="{{ request()->fullUrlWithQuery(['filter' => '']) }}" 
                       class="btn btn-sm {{ request('filter') === '' || !request('filter') ? 'btn-primary' : 'btn-outline-primary' }}">
                        All Conversations
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['filter' => 'unread']) }}" 
                       class="btn btn-sm {{ request('filter') === 'unread' ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="bi bi-circle-fill me-1"></i>Unread Only
                    </a>
                </div>
                @if(request('product'))
                    <a href="{{ request()->fullUrlWithQuery(['product' => '']) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle me-1"></i>Clear Product Filter
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 conversation-table">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">#</th>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Latest Message</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conversations as $conversation)
                        <tr class="conversation-row {{ $conversation['unread_count'] > 0 ? 'table-warning' : '' }}">
                            <td class="text-center text-muted">{{ $conversation['latest_message']->id }}</td>
                            <td style="min-width:180px;max-width:220px;">
                                <div class="d-flex align-items-center gap-2">
                                    @if($conversation['product'] && $conversation['product']->media && $conversation['product']->media->count() > 0)
                                        <img src="{{ $conversation['product']->media->first()->getUrl() }}" alt="Product" class="rounded me-2" style="width:38px;height:38px;object-fit:cover;">
                                    @else
                                        <div class="bg-light border rounded me-2 d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                                            <i class="bi bi-box text-secondary"></i>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <span class="fw-semibold text-dark text-truncate d-block product-name" style="max-width:120px;" title="{{ $conversation['product']->name ?? '-' }}">
                                            {{ \Illuminate\Support\Str::limit($conversation['product']->name ?? '-', 22) }}
                                        </span>
                                        <span class="badge bg-light text-muted border small">#{{ $conversation['product']->id ?? '-' }}</span>
                                        <a href="?product={{ $conversation['product']->id ?? '' }}" class="btn btn-link btn-xs p-0 ms-1 small">All for this product</a>
                                    </div>
                                </div>
                            </td>
                            <td style="min-width:120px;max-width:180px;">
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold small text-dark">{{ $conversation['other_user']->name ?? '-' }}</span>
                                    <span class="text-muted small" title="{{ $conversation['other_user']->email ?? '' }}">
                                        <i class="bi bi-envelope me-1"></i>{{ \Illuminate\Support\Str::limit($conversation['other_user']->email ?? '', 22) }}
                                    </span>
                                    @if($conversation['unread_count'] > 0)
                                        <span class="badge bg-warning text-dark small mt-1">
                                            <i class="bi bi-circle-fill me-1"></i>{{ $conversation['unread_count'] }} new
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td style="min-width:180px;max-width:260px;">
                                <span class="text-dark {{ $conversation['unread_count'] > 0 ? 'fw-semibold' : '' }}">
                                    {{ \Illuminate\Support\Str::limit($conversation['latest_message']->body ?? '-', 48) }}
                                </span>
                                <div class="text-muted small mt-1">
                                    <i class="bi bi-clock me-1"></i>{{ $conversation['latest_message']->created_at->diffForHumans() }}
                                    <span class="ms-2">
                                        <i class="bi bi-chat-dots me-1"></i>{{ $conversation['total_messages'] }} message{{ $conversation['total_messages'] > 1 ? 's' : '' }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    @if($conversation['unread_count'] > 0)
                                        <form method="POST" action="{{ route('seller.messages.mark-read', $conversation['latest_message']->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-sm px-3 shadow-sm mark-read-btn" title="Mark as Read">
                                                <i class="bi bi-check-circle me-1"></i> Mark Read
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('seller.messages.show', $conversation['conversation_id']) }}" class="btn btn-success btn-sm px-3 shadow-sm reply-btn">
                                        <i class="bi bi-chat-dots me-1"></i> View & Reply
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox me-2"></i> No conversations found for your products.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('styles')
<style>
    .conversation-table th, .conversation-table td {
        vertical-align: middle;
    }
    .conversation-row:hover {
        background: #f6fafd !important;
        transition: background 0.2s;
    }
    .conversation-row.table-warning {
        background-color: #fff3cd !important;
    }
    .conversation-row.table-warning:hover {
        background-color: #ffeaa7 !important;
    }
    .product-name {
        font-size: 1rem;
        font-weight: 500;
    }
    .reply-btn {
        transition: box-shadow 0.2s, background 0.2s;
    }
    .reply-btn:hover {
        box-shadow: 0 2px 8px rgba(40,167,69,0.12);
        background: #198754;
        color: #fff;
    }
    .mark-read-btn {
        transition: box-shadow 0.2s, background 0.2s;
    }
    .mark-read-btn:hover {
        box-shadow: 0 2px 8px rgba(255,193,7,0.12);
        background: #ffc107;
        color: #000;
    }
    @media (max-width: 768px) {
        .conversation-table td, .conversation-table th {
            font-size: 0.95rem;
            padding: 0.5rem 0.3rem;
        }
        .product-name {
            max-width: 70px !important;
        }
        .d-flex.gap-2 {
            flex-direction: column;
            gap: 0.5rem !important;
        }
        .d-flex.gap-2 .btn {
            width: 100%;
        }
    }
</style>
@endpush
@endsection 