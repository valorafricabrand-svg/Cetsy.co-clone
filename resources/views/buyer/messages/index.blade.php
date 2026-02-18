@extends('theme.'.theme().'.layouts.app')

@section('header')
    <h2 class="text-2xl font-semibold text-slate-900">
        {{ __('Your Conversations') }}
    </h2>
@endsection

@section('main')
<div class="py-8">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
        <div class="mx-auto grid max-w-6xl grid-cols-12 gap-4">
            <div class="col-span-12">
                @if(session('success'))
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="mb-4 rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-4 sm:p-5">
                        <h5 class="text-lg font-semibold text-slate-900">Conversations</h5>
                        <p class="text-sm text-slate-500">Here you can see all your conversations with sellers about specific products.</p>

                        <form method="GET" action="" class="mt-3 flex max-w-xl flex-wrap items-center gap-2">
                            <input type="text" name="search" value="{{ request('search') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 sm:flex-1" placeholder="Search user, product, or message...">
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-emerald-600 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50"><i class="bi bi-search mr-1"></i>Search</button>
                            @if(request('search'))
                                <a href="{{ request()->fullUrlWithQuery(['search' => '']) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50" title="Clear search">
                                    <i class="bi bi-x mr-1"></i>Clear
                                </a>
                            @endif
                        </form>
                    </div>
                </div>

                @if($conversations->isEmpty())
                    <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-8 text-center text-sm text-sky-800">
                        <img src="https://cdn-icons-png.flaticon.com/512/4076/4076549.png" alt="No messages" class="mx-auto w-20 opacity-50">
                        <div class="mt-3">You have no conversations yet.</div>
                        <div class="mt-2">
                            <a href="{{ route('listings') }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-500">
                                <i class="bi bi-search mr-1"></i>Browse Products
                            </a>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($conversations as $conversation)
                            <article class="conversation-card h-full rounded-2xl border border-slate-200 bg-white shadow-sm">
                                <div class="flex min-h-[230px] flex-col p-4 sm:p-5">
                                    <div class="mb-2 flex items-center">
                                        <div class="avatar avatar-border mr-2 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-base font-semibold text-white shadow">
                                            {{ strtoupper(substr($conversation['other_user']->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div class="grow">
                                            <div class="mb-0 text-base font-bold text-slate-900">
                                                {{ $conversation['shop'] ? $conversation['shop']->name : ($conversation['other_user']->name ?? 'Unknown') }}
                                                @if($conversation['unread_count'] > 0)
                                                    <span class="ml-1 inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-xs font-medium text-rose-700">{{ $conversation['unread_count'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <small class="text-xs text-slate-500">{{ $conversation['latest_message']->created_at->diffForHumans() }}</small>
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
                                                <img src="{{ $thumb }}" alt="{{ $conversation['product']->name }}" class="mr-2 h-9 w-9 rounded object-cover">
                                            @else
                                                <div class="mr-2 flex h-9 w-9 items-center justify-center rounded border bg-slate-100">
                                                    <i class="bi bi-box text-slate-600"></i>
                                                </div>
                                            @endif
                                            <span class="product-badge inline-flex max-w-[180px] items-center rounded-full bg-sky-100 px-3 py-1 text-xs font-medium text-sky-700" title="{{ $conversation['product']->name }}">
                                                {{ \Illuminate\Support\Str::limit($conversation['product']->name, 30) }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="mb-2 flex items-center">
                                            <div class="mr-2 flex h-9 w-9 items-center justify-center rounded border bg-slate-100">
                                                <i class="bi bi-chat-dots text-slate-600"></i>
                                            </div>
                                            <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">Direct Message</span>
                                        </div>
                                    @endif

                                    <div class="mb-2 grow">
                                        <span class="text-sm text-slate-900">{{ \Illuminate\Support\Str::limit($conversation['latest_message']->body, 80) }}</span>
                                    </div>

                                    <div class="mt-auto flex items-center justify-between">
                                        <small class="text-xs text-slate-500">
                                            {{ $conversation['total_messages'] }} message{{ $conversation['total_messages'] > 1 ? 's' : '' }}
                                        </small>
                                        <a href="{{ route('buyer.messages.show', $conversation['conversation_id']) }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-500">
                                            <i class="bi bi-chat-dots mr-1"></i>View
                                        </a>
                                    </div>
                                </div>
                            </article>
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
    }
    .conversation-card:hover {
        box-shadow: 0 6px 24px rgba(60,72,88,0.15);
        transform: translateY(-2px) scale(1.01);
    }
    .avatar {
        letter-spacing: 1px;
    }
    .avatar-border {
        border: 2px solid #e2e8f0;
    }
    .product-badge {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>
@endpush
@endsection
