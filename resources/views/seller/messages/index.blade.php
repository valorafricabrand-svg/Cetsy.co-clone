@extends('theme.'.theme().'.layouts.app')
@section('title', 'Customer Conversations')

@section('main')
@php
    $unreadCount = $conversations->sum('unread_count');
    $totalCount = $conversations->count();
@endphp

<section class="bg-slate-50 py-8 md:py-10">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
        <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
            @include('seller.partials.sidebar')

            <div class="space-y-5">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Customer Conversations</h1>
                            <p class="mt-1 text-sm text-slate-500">View and reply to conversations with customers about your products.</p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                <i class="fa-regular fa-comments mr-1.5"></i>{{ $totalCount }} conversation{{ $totalCount === 1 ? '' : 's' }}
                            </span>
                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $unreadCount > 0 ? 'border-amber-200 bg-amber-100 text-amber-800' : 'border-emerald-200 bg-emerald-100 text-emerald-800' }}">
                                <i class="fa-solid {{ $unreadCount > 0 ? 'fa-circle' : 'fa-check' }} mr-1.5 text-[8px]"></i>
                                {{ $unreadCount }} unread
                            </span>
                            @if($unreadCount > 0)
                                <form method="POST" action="{{ route('seller.messages.bulk-mark-read') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center rounded-xl border border-amber-300 bg-amber-100 px-3 py-2 text-xs font-semibold text-amber-900 transition hover:bg-amber-200" onclick="return confirm('Mark all unread messages as read?')">
                                        <i class="fa-regular fa-circle-check mr-1.5"></i>Mark all as read
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                @if(session('success'))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
                @endif
                @if(session('warning'))
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">{{ session('warning') }}</div>
                @endif

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="inline-flex items-center text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                            <i class="fa-solid fa-filter mr-2"></i>Filter
                        </span>

                        <div class="inline-flex items-center gap-1 rounded-xl border border-slate-300 p-1">
                            <a href="{{ request()->fullUrlWithQuery(['filter' => '']) }}" class="inline-flex items-center rounded-lg px-3 py-1.5 text-sm font-semibold transition {{ request('filter') === 'unread' ? 'text-slate-700 hover:bg-slate-100' : 'bg-emerald-600 text-white hover:bg-emerald-700' }}">All Conversations</a>
                            <a href="{{ request()->fullUrlWithQuery(['filter' => 'unread']) }}" class="inline-flex items-center rounded-lg px-3 py-1.5 text-sm font-semibold transition {{ request('filter') === 'unread' ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'text-slate-700 hover:bg-slate-100' }}">
                                <i class="fa-solid fa-circle mr-1 text-[8px]"></i>Unread Only
                            </a>
                        </div>

                        @if(request('product'))
                            <a href="{{ request()->fullUrlWithQuery(['product' => '']) }}" class="inline-flex items-center rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                <i class="fa-solid fa-xmark-circle mr-1.5"></i>Clear product filter
                            </a>
                        @endif

                        <form method="GET" action="{{ route('seller.messages.index') }}" class="ml-auto flex w-full flex-wrap items-center gap-2 sm:w-auto">
                            <input type="hidden" name="filter" value="{{ request('filter') }}">
                            <input type="hidden" name="product" value="{{ request('product') }}">
                            <div class="relative w-full sm:w-80">
                                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="text" name="search" value="{{ request('search') }}" class="w-full rounded-xl border border-slate-300 bg-white py-2 pl-9 pr-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" placeholder="Search user, product, or message...">
                            </div>
                            <button type="submit" class="inline-flex items-center rounded-xl border border-emerald-600 bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                <i class="fa-solid fa-search mr-1.5"></i>Search
                            </button>
                            @if(request('search'))
                                <a href="{{ request()->fullUrlWithQuery(['search' => '']) }}" class="inline-flex items-center rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                    <i class="fa-solid fa-xmark mr-1"></i>Clear
                                </a>
                            @endif
                        </form>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    @if($conversations->isEmpty())
                        <div class="px-6 py-16 text-center">
                            <i class="fa-solid fa-inbox text-3xl text-slate-300"></i>
                            <p class="mt-3 text-sm text-slate-500">No conversations found for your products.</p>
                        </div>
                    @else
                        <div class="hidden overflow-x-auto md:block">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-slate-50 text-slate-600">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold">#</th>
                                        <th class="px-4 py-3 text-left font-semibold">Product</th>
                                        <th class="px-4 py-3 text-left font-semibold">Customer</th>
                                        <th class="px-4 py-3 text-left font-semibold">Latest Message</th>
                                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                                        <th class="px-4 py-3 text-right font-semibold">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($conversations as $conversation)
                                        @php
                                            $product = $conversation['product'];
                                            $latest = $conversation['latest_message'];
                                            $otherUser = $conversation['other_user'];
                                            $unread = (int) $conversation['unread_count'] > 0;
                                            $thumb = null;
                                            if ($product) {
                                                $thumb = function_exists('product_thumb_url')
                                                    ? product_thumb_url($product)
                                                    : (optional(optional($product)->media->first())->url ? asset('storage/' . $product->media->first()->url) : null);
                                            }
                                        @endphp

                                        <tr class="align-top {{ $unread ? 'bg-amber-50/60' : 'bg-white' }} hover:bg-slate-50">
                                            <td class="px-4 py-3 text-slate-500">{{ $latest->id }}</td>

                                            <td class="px-4 py-3">
                                                <div class="flex items-start gap-3">
                                                    @if($thumb)
                                                        <img src="{{ $thumb }}" alt="{{ $product->name ?? 'Product' }}" class="h-11 w-11 rounded-xl border border-slate-200 object-cover">
                                                    @else
                                                        <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-400">
                                                            <i class="fa-solid {{ $product ? 'fa-box' : 'fa-comments' }}"></i>
                                                        </div>
                                                    @endif

                                                    <div class="min-w-0">
                                                        @if($product)
                                                            <p class="truncate text-sm font-semibold text-slate-900" title="{{ $product->name ?? '-' }}">{{ \Illuminate\Support\Str::limit($product->name ?? '-', 28) }}</p>
                                                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                                                <span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-600">#{{ $product->id ?? '-' }}</span>
                                                                <a href="{{ request()->fullUrlWithQuery(['product' => $product->id]) }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-600 hover:underline">All for this product</a>
                                                            </div>
                                                        @else
                                                            <span class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700">Direct Message</span>
                                                            <p class="mt-1 text-xs text-slate-500">No specific product</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="px-4 py-3">
                                                <p class="text-sm font-semibold text-slate-900">{{ $otherUser->name ?? '-' }}</p>
                                                <p class="mt-0.5 text-xs text-slate-500" title="{{ $otherUser->email ?? '' }}">
                                                    <i class="fa-regular fa-envelope mr-1"></i>{{ \Illuminate\Support\Str::limit($otherUser->email ?? '', 28) }}
                                                </p>
                                                @if($unread)
                                                    <span class="mt-1 inline-flex items-center rounded-full border border-amber-200 bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800">
                                                        <i class="fa-solid fa-circle mr-1 text-[8px]"></i>{{ $conversation['unread_count'] }} new
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="px-4 py-3">
                                                <p class="text-sm {{ $unread ? 'font-semibold text-slate-900' : 'text-slate-700' }}">{{ \Illuminate\Support\Str::limit($latest->body ?? '-', 62) }}</p>
                                                <p class="mt-1 text-xs text-slate-500">
                                                    <i class="fa-regular fa-clock mr-1"></i>{{ optional($latest->created_at)->diffForHumans() }}
                                                    <span class="ml-2"><i class="fa-regular fa-comments mr-1"></i>{{ $conversation['total_messages'] }} message{{ $conversation['total_messages'] == 1 ? '' : 's' }}</span>
                                                </p>
                                            </td>

                                            <td class="px-4 py-3">
                                                @if($unread)
                                                    <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">
                                                        <i class="fa-solid fa-circle mr-1 text-[8px]"></i>Unread
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">
                                                        <i class="fa-solid fa-check mr-1 text-[10px]"></i>Read
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="px-4 py-3">
                                                <div class="flex flex-wrap items-center justify-end gap-2">
                                                    @if($unread)
                                                        <form method="POST" action="{{ route('seller.messages.mark-read', $latest->id) }}">
                                                            @csrf
                                                            <button type="submit" class="inline-flex items-center rounded-lg border border-amber-300 bg-amber-100 px-2.5 py-1.5 text-xs font-semibold text-amber-900 hover:bg-amber-200" title="Mark as Read">
                                                                <i class="fa-regular fa-circle-check mr-1"></i>Mark Read
                                                            </button>
                                                        </form>
                                                    @elseif(!empty($conversation['last_received_message_id']))
                                                        <form method="POST" action="{{ route('seller.messages.mark-unread', $conversation['last_received_message_id']) }}">
                                                            @csrf
                                                            <button type="submit" class="inline-flex items-center rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100" title="Mark as Unread">
                                                                <i class="fa-solid fa-circle mr-1 text-[9px]"></i>Mark Unread
                                                            </button>
                                                        </form>
                                                    @endif

                                                    <a href="{{ route('seller.messages.show', $conversation['conversation_id']) }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                                                        <i class="fa-regular fa-comments mr-1"></i>View & Reply
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="space-y-3 p-3 md:hidden">
                            @foreach($conversations as $conversation)
                                @php
                                    $product = $conversation['product'];
                                    $latest = $conversation['latest_message'];
                                    $otherUser = $conversation['other_user'];
                                    $unread = (int) $conversation['unread_count'] > 0;
                                    $thumb = null;
                                    if ($product) {
                                        $thumb = function_exists('product_thumb_url')
                                            ? product_thumb_url($product)
                                            : (optional(optional($product)->media->first())->url ? asset('storage/' . $product->media->first()->url) : null);
                                    }
                                @endphp

                                <article class="rounded-xl border {{ $unread ? 'border-amber-200 bg-amber-50/60' : 'border-slate-200 bg-white' }} p-3">
                                    <div class="flex items-start gap-3">
                                        @if($thumb)
                                            <img src="{{ $thumb }}" alt="{{ $product->name ?? 'Product' }}" class="h-12 w-12 rounded-xl border border-slate-200 object-cover">
                                        @else
                                            <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-400">
                                                <i class="fa-solid {{ $product ? 'fa-box' : 'fa-comments' }}"></i>
                                            </div>
                                        @endif

                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-semibold text-slate-900">{{ $product ? \Illuminate\Support\Str::limit($product->name, 34) : 'Direct Message' }}</p>
                                            <p class="mt-0.5 text-xs text-slate-500">{{ $otherUser->name ?? '-' }} · {{ \Illuminate\Support\Str::limit($otherUser->email ?? '', 26) }}</p>
                                            <p class="mt-1 text-sm {{ $unread ? 'font-semibold text-slate-900' : 'text-slate-700' }}">{{ \Illuminate\Support\Str::limit($latest->body ?? '-', 80) }}</p>
                                            <p class="mt-1 text-xs text-slate-500">
                                                <i class="fa-regular fa-clock mr-1"></i>{{ optional($latest->created_at)->diffForHumans() }}
                                                <span class="ml-2"><i class="fa-regular fa-comments mr-1"></i>{{ $conversation['total_messages'] }}</span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-3 flex flex-wrap items-center gap-2">
                                        @if($unread)
                                            <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800">
                                                <i class="fa-solid fa-circle mr-1 text-[8px]"></i>{{ $conversation['unread_count'] }} unread
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800">
                                                <i class="fa-solid fa-check mr-1 text-[10px]"></i>Read
                                            </span>
                                        @endif

                                        @if($unread)
                                            <form method="POST" action="{{ route('seller.messages.mark-read', $latest->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center rounded-lg border border-amber-300 bg-amber-100 px-2.5 py-1.5 text-xs font-semibold text-amber-900 hover:bg-amber-200">
                                                    <i class="fa-regular fa-circle-check mr-1"></i>Mark Read
                                                </button>
                                            </form>
                                        @elseif(!empty($conversation['last_received_message_id']))
                                            <form method="POST" action="{{ route('seller.messages.mark-unread', $conversation['last_received_message_id']) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                                    <i class="fa-solid fa-circle mr-1 text-[9px]"></i>Mark Unread
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('seller.messages.show', $conversation['conversation_id']) }}" class="ml-auto inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                                            <i class="fa-regular fa-comments mr-1"></i>Open
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
