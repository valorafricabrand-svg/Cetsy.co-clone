@extends('layouts.app')

@section('header')
    <h2 class="fw-semibold fs-3 text-dark">
        {{ __('Your Messages') }}
    </h2>
@endsection

@section('content')
<div class="content">
    <div class="content-xxl">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">
                @if(session('success'))
                    <div class="alert alert-success mb-4">
                        {{ session('success') }}
                    </div>
                @endif
                <div class="row">
                    <div class="col-12" style="margin-bottom: 10px;">
                        <div class="card shadow-sm border-0" >
                            <div class="card-body">
                                <h5 class="card-title">List of Messages</h5>
                                <p class="card-text">Here you can see all the messages you have received from sellers.</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($messages->isEmpty())
                    <div class="alert alert-info text-center py-5">
                        <img src="https://cdn-icons-png.flaticon.com/512/4076/4076549.png" alt="No messages" style="width:80px;opacity:0.5;">
                        <div class="mt-3">You have no messages yet.</div>
                    </div>
                @else
                    <div class="row g-3">
                        @foreach($messages as $message)
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card h-100 shadow-sm border-0 message-card position-relative">
                                    <div class="card-body d-flex flex-column" style="min-height: 230px;">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="avatar bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2 shadow avatar-border" style="width:40px;height:40px;font-size:1.2rem;">
                                                {{ strtoupper(substr($message->sender->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold mb-0" style="font-size:1.1rem;">
                                                    {{ $message->sender->name ?? 'Unknown' }}
                                                    @if(isset($message->is_read) && !$message->is_read)
                                                        <span class="unread-dot ms-1"></span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="ms-auto text-end">
                                                <small class="text-muted" style="font-size:0.9rem;">{{ $message->created_at->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                        @if($message->product)
                                            <div class="mb-2">
                                                <span class="badge rounded-pill bg-primary text-white px-3 py-2 product-badge" style="font-size:0.85rem;max-width: 100%;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;cursor: pointer;" title="{{ $message->product->name }}">
                                                    {{ \Illuminate\Support\Str::limit($message->product->name, 30) }}
                                                </span>
                                            </div>
                                        @endif
                                        <div class="flex-grow-1 mb-2">
                                            <span class="text-dark" style="font-size:1rem;">
                                                {{ \Illuminate\Support\Str::limit($message->body, 80) }}
                                            </span>
                                        </div>
                                        <div class="mt-auto">
                                            <a href="{{ route('buyer.messages.chat.show', $message->id) }}" class="btn btn-success btn-sm w-100 w-md-auto px-4 mt-2 mt-md-0">
                                                <i class="bi bi-chat-dots me-1"></i> View Conversation
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .message-card {
        transition: box-shadow 0.2s, transform 0.2s;
        min-height: 260px;
    }
    .message-card:hover {
        box-shadow: 0 6px 24px rgba(60,72,88,0.15);
        transform: translateY(-2px) scale(1.02);
    }
    .avatar {
        font-weight: 600;
        letter-spacing: 1px;
        border: 2px solid #fff;
        box-shadow: 0 2px 8px rgba(60,72,88,0.10);
    }
    .avatar-border {
        border: 2px solid #e0e0e0;
    }
    .product-badge {
        display: inline-block;
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
    }
    .unread-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        background: #007bff;
        border-radius: 50%;
        vertical-align: middle;
        margin-left: 2px;
    }
    @media (max-width: 767.98px) {
        .btn.w-100.w-md-auto {
            width: 100% !important;
        }
    }
    @media (min-width: 768px) {
        .btn.w-100.w-md-auto {
            width: auto !important;
        }
    }
</style>
@endpush
@endsection
