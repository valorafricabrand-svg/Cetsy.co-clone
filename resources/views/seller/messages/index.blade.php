@extends('layouts.app')
@section('title', 'Customer Messages')

@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">Customer Messages</h1>
            <p class="text-muted mb-0">View and reply to messages from customers about your products</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    <div class="card shadow border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 message-table">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">#</th>
                        <th>Product</th>
                        <th>From</th>
                        <th>Message</th>
                        <th class="text-end">Reply</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $message)
                        <tr class="message-row">
                            <td class="text-center text-muted">{{ $message->id }}</td>
                            <td style="min-width:180px;max-width:220px;">
                                <div class="d-flex align-items-center gap-2">
                                    @if($message->product && $message->product->image)
                                        <img src="{{ asset('storage/' . $message->product->image) }}" alt="Product" class="rounded me-2" style="width:38px;height:38px;object-fit:cover;">
                                    @else
                                        <div class="bg-light border rounded me-2 d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                                            <i class="bi bi-box text-secondary"></i>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <span class="fw-semibold text-dark text-truncate d-block product-name" style="max-width:120px;" title="{{ $message->product->name ?? '-' }}">
                                            {{ \Illuminate\Support\Str::limit($message->product->name ?? '-', 22) }}
                                        </span>
                                        <span class="badge bg-light text-muted border small">#{{ $message->product_id }}</span>
                                        <a href="?product={{ $message->product_id }}" class="btn btn-link btn-xs p-0 ms-1 small">All for this product</a>
                                    </div>
                                </div>
                            </td>
                            <td style="min-width:120px;max-width:180px;">
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold small text-dark">{{ $message->sender->name ?? '-' }}</span>
                                    <span class="text-muted small" title="{{ $message->sender->email ?? '' }}">
                                        <i class="bi bi-envelope me-1"></i>{{ \Illuminate\Support\Str::limit($message->sender->email ?? '', 22) }}
                                    </span>
                                </div>
                            </td>
                            <td style="min-width:180px;max-width:260px;">
                                <span class="text-dark">{{ \Illuminate\Support\Str::limit($message->body ?? $message->content ?? '-', 48) }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('seller.messages.show', $message->id) }}" class="btn btn-success btn-sm px-3 shadow-sm reply-btn">
                                    <i class="bi bi-chat-dots me-1"></i> Reply
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox me-2"></i> No messages found for your products.
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
    .message-table th, .message-table td {
        vertical-align: middle;
    }
    .message-row:hover {
        background: #f6fafd !important;
        transition: background 0.2s;
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
    @media (max-width: 768px) {
        .message-table td, .message-table th {
            font-size: 0.95rem;
            padding: 0.5rem 0.3rem;
        }
        .product-name {
            max-width: 70px !important;
        }
    }
</style>
@endpush
@endsection 