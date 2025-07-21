@extends('layouts.app')

@section('header')
    <h2 class="fw-semibold fs-3 text-dark">
        {{ __('Your Conversations') }}
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
                                <h5 class="card-title">Conversations</h5>
                                <p class="card-text">Here you can see all your conversations with sellers about specific products.</p>
                                <form method="GET" action="" class="d-flex align-items-center gap-2 flex-wrap mt-2" style="max-width:400px;">
                                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search user, product, or message...">
                                    <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-search"></i> Search</button>
                                    @if(request('search'))
                                        <a href="{{ request()->fullUrlWithQuery(['search' => '']) }}"
                                           class="btn btn-outline-secondary btn-sm"
                                           style="min-width:32px; margin-left:2px;"
                                           title="Clear search">
                                            <i class="bi bi-x"></i> Clear
                                        </a>
                                    @endif
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                @if($conversations->isEmpty())
                    <div class="alert alert-info text-center py-5">
                        <img src="https://cdn-icons-png.flaticon.com/512/4076/4076549.png" alt="No messages" style="width:80px;opacity:0.5;">
                        <div class="mt-3">You have no conversations yet.</div>
                        <div class="mt-2">
                            <a href="{{ route('listings') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-search me-1"></i>Browse Products
                            </a>
                        </div>
                    </div>
                @else
                    <div class="row g-3">
                        @foreach($conversations as $conversation)
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card h-100 shadow-sm border-0 conversation-card position-relative">
                                    <div class="card-body d-flex flex-column" style="min-height: 230px;">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="avatar bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2 shadow avatar-border" style="width:40px;height:40px;font-size:1.2rem;">
                                                {{ strtoupper(substr($conversation['other_user']->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold mb-0" style="font-size:1.1rem;">
                                                    {{ $conversation['shop'] ? $conversation['shop']->name : ($conversation['other_user']->name ?? 'Unknown') }}
                                                    @if($conversation['unread_count'] > 0)
                                                        <span class="badge bg-danger ms-1">{{ $conversation['unread_count'] }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="ms-auto text-end">
                                                <small class="text-muted" style="font-size:0.9rem;">{{ $conversation['latest_message']->created_at->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                        
                                        @if($conversation['product'])
                                            <div class="mb-2">
                                                <span class="badge rounded-pill bg-primary text-white px-3 py-2 product-badge" style="font-size:0.85rem;max-width: 100%;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;cursor: pointer;" title="{{ $conversation['product']->name }}">
                                                    {{ \Illuminate\Support\Str::limit($conversation['product']->name, 30) }}
                                                </span>
                                            </div>
                                        @endif
                                        
                                        <div class="flex-grow-1 mb-2">
                                            <span class="text-dark" style="font-size:1rem;">
                                                {{ \Illuminate\Support\Str::limit($conversation['latest_message']->body, 80) }}
                                            </span>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center mt-auto">
                                            <small class="text-muted">
                                                {{ $conversation['total_messages'] }} message{{ $conversation['total_messages'] > 1 ? 's' : '' }}
                                            </small>
                                            <a href="{{ route('buyer.messages.show', $conversation['conversation_id']) }}" class="btn btn-success btn-sm px-4">
                                                <i class="bi bi-chat-dots me-1"></i> View
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
    .conversation-card {
        transition: box-shadow 0.2s, transform 0.2s;
        min-height: 260px;
    }
    .conversation-card:hover {
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
    .badge {
        font-size: 0.75rem;
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
