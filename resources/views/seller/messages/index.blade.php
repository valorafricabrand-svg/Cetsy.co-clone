@extends('theme.'.theme().'.layouts.app')
@section('title', 'Customer Conversations')

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')
      <div class="space-y-6">
<div class="content">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="h4 mb-1">Customer Conversations</h1>
            <p class="text-slate-500 mb-0">View and reply to conversations with customers about your products</p>
            @php
                $unreadCount = $conversations->sum('unread_count');
                $totalCount = $conversations->count();
            @endphp
            @if($unreadCount > 0)
                <div class="mt-2">
                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-amber-100 text-amber-800 border-amber-200 text-slate-900">
                        <i class="bi bi-circle-fill mr-1"></i>{{ $unreadCount }} new message{{ $unreadCount > 1 ? 's' : '' }}
                    </span>
                    <span class="text-slate-500 text-xs ml-2">in {{ $totalCount }} conversation{{ $totalCount > 1 ? 's' : '' }}</span>
                </div>
            @endif
        </div>
        @if($unreadCount > 0)
            <div>
                <form method="POST" action="{{ route('seller.messages.bulk-mark-read') }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-amber-500 bg-amber-500 text-slate-900 hover:bg-amber-400 px-2.5 py-1.5 text-xs rounded-lg" onclick="return confirm('Mark all unread messages as read?')">
                        <i class="bi bi-check-circle mr-1"></i>Mark All as Read
                    </button>
                </form>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800">{{ session('warning') }}</div>
    @endif

    {{-- Filter Section --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow border-0 mb-3">
        <div class="p-4 p-3">
            <div class="flex items-center gap-3 flex-wrap">
                <div class="flex items-center">
                    <i class="bi bi-funnel mr-2 text-slate-500"></i>
                    <span class="text-slate-500 text-xs">Filter:</span>
                </div>
                <div class="inline-flex items-center gap-1 rounded-xl border border-slate-300 p-1" role="group">
                    <a href="{{ request()->fullUrlWithQuery(['filter' => '']) }}" 
                       class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition btn-sm {{ request('filter') === '' || !request('filter') ? 'btn-primary' : 'btn-outline-primary' }}">
                        All Conversations
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['filter' => 'unread']) }}" 
                       class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition btn-sm {{ request('filter') === 'unread' ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="bi bi-circle-fill mr-1"></i>Unread Only
                    </a>
                </div>
                @if(request('product'))
                    <a href="{{ request()->fullUrlWithQuery(['product' => '']) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100 px-2.5 py-1.5 text-xs rounded-lg">
                        <i class="bi bi-x-circle mr-1"></i>Clear Product Filter
                    </a>
                @endif
                <form method="GET" action="" class="ml-auto flex items-center gap-2 flex-wrap" style="max-width:350px;">
                    <input type="hidden" name="filter" value="{{ request('filter') }}">
                    <input type="hidden" name="product" value="{{ request('product') }}">
                    <input type="text" name="search" value="{{ request('search') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" placeholder="Search user, product, or message...">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50 px-2.5 py-1.5 text-xs rounded-lg"><i class="bi bi-search"></i> Search</button>
                    @if(request('search'))
                        <a href="{{ request()->fullUrlWithQuery(['search' => '']) }}"
                           class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100 px-2.5 py-1.5 text-xs rounded-lg"
                           style="min-width:32px; margin-left:2px;"
                           title="Clear search">
                            <i class="bi bi-x"></i> Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow border-0">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm table-hover align-middle mb-0 conversation-table">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-center">#</th>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Latest Message</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conversations as $conversation)
                        <tr class="conversation-row {{ $conversation['unread_count'] > 0 ? 'table-warning' : '' }}">
                            <td class="text-center text-slate-500">{{ $conversation['latest_message']->id }}</td>
                            <td style="min-width:180px;max-width:220px;">
                                <div class="flex items-center gap-2">
                                    @php
                                        $thumb = null;
                                        if (!empty($conversation['product'])) {
                                            $thumb = function_exists('product_thumb_url')
                                                ? product_thumb_url($conversation['product'])
                                                : (optional(optional($conversation['product'])->media->first())->url ? asset('storage/' . $conversation['product']->media->first()->url) : null);
                                        }
                                    @endphp
                                    @if($thumb)
                                        <img src="{{ $thumb }}" alt="{{ $conversation['product']->name ?? 'Product' }}" class="rounded mr-2" style="width:38px;height:38px;object-fit:cover;">
                                    @else
                                        @if($conversation['product'])
                                            <div class="bg-slate-50 border rounded mr-2 flex items-center justify-center" style="width:38px;height:38px;">
                                                <i class="bi bi-box text-secondary"></i>
                                            </div>
                                        @else
                                            <div class="bg-slate-50 border rounded mr-2 flex items-center justify-center" style="width:38px;height:38px;">
                                                <i class="bi bi-chat-dots text-secondary"></i>
                                            </div>
                                        @endif
                                    @endif
                                    <div class="flex-grow-1">
                                        @if($conversation['product'])
                                            <span class="font-semibold text-slate-900 text-truncate block product-name" style="max-width:120px;" title="{{ $conversation['product']->name ?? '-' }}">
                                                {!! \Illuminate\Support\Str::limit($conversation['product']->name ?? '-', 22) !!}
                                            </span>
                                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-slate-50 text-slate-500 text-xs">#{{ $conversation['product']->id ?? '-' }}</span>
                                            <a href="?product={{ $conversation['product']->id ?? '' }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition text-emerald-700 hover:underline btn-xs p-0 ml-1 text-xs">All for this product</a>
                                        @else
                                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-900 text-xs">Direct Message</span>
                                            <div class="text-xs text-slate-500">No specific product</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td style="min-width:120px;max-width:180px;">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-xs text-slate-900">{{ $conversation['other_user']->name ?? '-' }}</span>
                                    <span class="text-slate-500 text-xs" title="{{ $conversation['other_user']->email ?? '' }}">
                                        <i class="bi bi-envelope mr-1"></i>{{ \Illuminate\Support\Str::limit($conversation['other_user']->email ?? '', 22) }}
                                    </span>
                                    @if($conversation['unread_count'] > 0)
                                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-amber-100 text-amber-800 border-amber-200 text-slate-900 text-xs mt-1">
                                            <i class="bi bi-circle-fill mr-1"></i>{{ $conversation['unread_count'] }} new
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td style="min-width:180px;max-width:260px;">
                                <span class="text-slate-900 {{ $conversation['unread_count'] > 0 ? 'font-semibold' : '' }}">
                                    {{ \Illuminate\Support\Str::limit($conversation['latest_message']->body ?? '-', 48) }}
                                </span>
                                <div class="text-slate-500 text-xs mt-1">
                                    <i class="bi bi-clock mr-1"></i>{{ $conversation['latest_message']->created_at->diffForHumans() }}
                                    <span class="ml-2">
                                        <i class="bi bi-chat-dots mr-1"></i>{{ $conversation['total_messages'] }} message{{ $conversation['total_messages'] > 1 ? 's' : '' }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($conversation['unread_count'] > 0)
                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-amber-100 text-amber-800 border-amber-200 text-slate-900"><i class="bi bi-dot"></i> Unread</span>
                                @else
                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-emerald-100 text-emerald-800 border-emerald-200"><i class="bi bi-check2"></i> Read</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex gap-2 justify-end">
                                    @if($conversation['unread_count'] > 0)
                                        <form method="POST" action="{{ route('seller.messages.mark-read', $conversation['latest_message']->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-amber-500 bg-amber-500 text-slate-900 hover:bg-amber-400 px-2.5 py-1.5 text-xs rounded-lg shadow-sm mark-read-btn" title="Mark as Read">
                                                <i class="bi bi-check-circle mr-1"></i> Mark Read
                                            </button>
                                        </form>
                                    @elseif(!empty($conversation['last_received_message_id']))
                                        <form method="POST" action="{{ route('seller.messages.mark-unread', $conversation['last_received_message_id']) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-slate-300 text-slate-700 hover:bg-slate-100 px-2.5 py-1.5 text-xs rounded-lg shadow-sm" title="Mark as Unread">
                                                <i class="bi bi-dot"></i> Mark Unread
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('seller.messages.show', $conversation['conversation_id']) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700 px-2.5 py-1.5 text-xs rounded-lg shadow-sm reply-btn">
                                        <i class="bi bi-chat-dots mr-1"></i> View & Reply
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-slate-500">
                                <i class="bi bi-inbox mr-2"></i> No conversations found for your products.
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
        .flex.gap-2 {
            flex-direction: column;
            gap: 0.5rem !important;
        }
        .flex.gap-2 .btn {
            width: 100%;
        }
    }
</style>
@endpush
      </div>
    </div>
  </div>
</section>
@endsection 






