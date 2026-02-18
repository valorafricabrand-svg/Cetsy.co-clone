@extends('theme.'.theme().'.layouts.app')

@section('header')
    <h2 class="font-semibold fs-3 text-slate-900">
        {{ __('Your Conversations') }}
    </h2>
@endsection

@section('main')
<div class="content">
    <div class="content-xxl">
        <div class="grid grid-cols-12 gap-4 justify-center">
            <div class="lg:col-span-10 md:col-span-12">
                @if(session('success'))
                    <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800 mb-4">
                        {{ session('success') }}
                    </div>
                @endif
                
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12" style="margin-bottom: 10px;">
                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0" >
                            <div class="p-4 sm:p-5">
                                <h5 class="text-lg font-semibold text-slate-900">Conversations</h5>
                                <p class="card-text">Here you can see all your conversations with sellers about specific products.</p>
                                <form method="GET" action="" class="flex items-center gap-2 flex-wrap mt-2" style="max-width:400px;">
                                    <input type="text" name="search" value="{{ request('search') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 px-2.5 py-1.5 text-xs" placeholder="Search user, product, or message...">
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50 px-3 py-1.5 text-xs"><i class="bi bi-search"></i> Search</button>
                                    @if(request('search'))
                                        <a href="{{ request()->fullUrlWithQuery(['search' => '']) }}"
                                           class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-50 px-3 py-1.5 text-xs"
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
                    <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 text-center py-5">
                        <img src="https://cdn-icons-png.flaticon.com/512/4076/4076549.png" alt="No messages" style="width:80px;opacity:0.5;">
                        <div class="mt-3">You have no conversations yet.</div>
                        <div class="mt-2">
                            <a href="{{ route('listings') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 px-3 py-1.5 text-xs">
                                <i class="bi bi-search mr-1"></i>Browse Products
                            </a>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-12 gap-4 gap-3">
                        @foreach($conversations as $conversation)
                            <div class="col-span-12 md:col-span-6 lg:col-span-4">
                                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm h-full border-0 conversation-card position-relative">
                                    <div class="p-4 sm:p-5 flex flex-col" style="min-height: 230px;">
                                        <div class="flex items-center mb-2">
                                            <div class="avatar bg-success text-white rounded-full flex items-center justify-center mr-2 shadow avatar-border" style="width:40px;height:40px;font-size:1.2rem;">
                                                {{ strtoupper(substr($conversation['other_user']->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="font-bold mb-0" style="font-size:1.1rem;">
                                                    {{ $conversation['shop'] ? $conversation['shop']->name : ($conversation['other_user']->name ?? 'Unknown') }}
                                                    @if($conversation['unread_count'] > 0)
                                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-danger ml-1">{{ $conversation['unread_count'] }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="ms-auto text-right">
                                                <small class="text-slate-500" style="font-size:0.9rem;">{{ $conversation['latest_message']->created_at->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                        
                                        @if($conversation['product'])
                                            @php
                                                $thumb = function_exists('product_thumb_url')
                                                    ? product_thumb_url($conversation['product'])
                                                    : (optional(optional($conversation['product'])->media->first())->url ? asset('storage/' . $conversation['product']->media->first()->url) : null);
                                            @endphp
                                            <div class="mb-2 flex items-center">
                                                @if($thumb)
                                                    <img src="{{ $thumb }}" alt="{{ $conversation['product']->name }}" class="rounded mr-2" style="width:36px;height:36px;object-fit:cover;">
                                                @else
                                                    <div class="bg-slate-100 border rounded mr-2 flex items-center justify-center" style="width:36px;height:36px;">
                                                        <i class="bi bi-box text-slate-600"></i>
                                                    </div>
                                                @endif
                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-primary text-white px-3 py-2 product-badge" style="font-size:0.85rem;max-width: 100%;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;cursor: pointer;" title="{{ $conversation['product']->name }}">
                                                    {{ \Illuminate\Support\Str::limit($conversation['product']->name, 30) }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="mb-2 flex items-center">
                                                <div class="bg-slate-100 border rounded mr-2 flex items-center justify-center" style="width:36px;height:36px;">
                                                    <i class="bi bi-chat-dots text-slate-600"></i>
                                                </div>
                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-secondary-subtle text-slate-900 border px-3 py-2">Direct Message</span>
                                            </div>
                                        @endif
                                        
                                        <div class="flex-grow-1 mb-2">
                                            <span class="text-slate-900" style="font-size:1rem;">
                                                {{ \Illuminate\Support\Str::limit($conversation['latest_message']->body, 80) }}
                                            </span>
                                        </div>
                                        
                                        <div class="flex justify-between items-center mt-auto">
                                            <small class="text-slate-500">
                                                {{ $conversation['total_messages'] }} message{{ $conversation['total_messages'] > 1 ? 's' : '' }}
                                            </small>
                                            <a href="{{ route('buyer.messages.show', $conversation['conversation_id']) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-emerald-600 text-white hover:bg-emerald-500 px-3 py-1.5 text-xs">
                                                <i class="bi bi-chat-dots mr-1"></i> View
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




