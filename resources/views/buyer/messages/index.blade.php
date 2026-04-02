@extends('theme.'.theme().'.layouts.app')

@section('header')
    <h2 class="text-2xl font-semibold text-slate-900">
        {{ __('Your Conversations') }}
    </h2>
@endsection

@section('main')
<div class="py-8">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 lg:col-span-3">
                @include('buyer.partials.sidebar')
            </div>
            <div class="col-span-12 lg:col-span-9">
                @if(session('success'))
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="mb-4 rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-4 sm:p-5">
                        <h5 class="text-lg font-semibold text-slate-900">Conversations</h5>
                        <p class="text-sm text-slate-500">Here you can see all your conversations with sellers about specific products.</p>

                        <form method="GET" action="" class="mt-3 flex max-w-xl flex-wrap items-center gap-2">
                            <input type="text" name="search" value="{{ request('search') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 sm:flex-1" placeholder="Search user, product, or message...">
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-emerald-600 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50"><i class="fa-solid fa-magnifying-glass mr-1"></i>Search</button>
                            @if(request('search'))
                                <a href="{{ request()->fullUrlWithQuery(['search' => '']) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50" title="Clear search">
                                    <i class="fa-solid fa-xmark mr-1"></i>Clear
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
                                <i class="fa-solid fa-magnifying-glass mr-1"></i>Browse Products
                            </a>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($conversations as $conversation)
                            @php
                                $product = $conversation['product'];
                                $latestOffer = $conversation['latest_offer'] ?? null;
                                $conversationUrl = route('buyer.messages.show', $conversation['conversation_id']);
                                $productUrl = $product ? route('listing.show', $product->slug ?: $product->id) : null;
                                $thumb = null;
                                $basePrice = 0.0;
                                $salePrice = 0.0;
                                $lowestVariantPrice = null;
                                $priceLabel = null;
                                $displayPrice = null;
                                $showComparePrice = false;
                                $offerCap = null;
                                $showOfferPanel = false;
                                $offerInputValue = old('offer_price', $latestOffer->offer_price ?? '');

                                if ($product) {
                                    $thumb = function_exists('product_thumb_url')
                                        ? product_thumb_url($product)
                                        : (optional(optional($product)->media->first())->url ? asset('storage/' . $product->media->first()->url) : null);

                                    $basePrice = (float) ($product->price ?? 0);
                                    $salePrice = (float) ($product->discounted_price ?? $basePrice);

                                    if ($product->relationLoaded('variations') && $product->variations) {
                                        $lowestVariantPrice = $product->variations
                                            ->pluck('price')
                                            ->filter(fn ($value) => $value !== null)
                                            ->min();
                                    } elseif (method_exists($product, 'variations')) {
                                        $lowestVariantPrice = $product->variations()
                                            ->whereNotNull('price')
                                            ->min('price');
                                    }

                                    if ($lowestVariantPrice !== null) {
                                        $displayPrice = (float) $lowestVariantPrice;
                                        $priceLabel = strtolower((string) ($product->type ?? '')) === 'service' ? 'Priced from' : 'From';
                                    } elseif ($salePrice > 0 && ($basePrice <= 0 || $salePrice < $basePrice)) {
                                        $displayPrice = $salePrice;
                                    } elseif ($basePrice > 0) {
                                        $displayPrice = $basePrice;
                                    } elseif ($salePrice > 0) {
                                        $displayPrice = $salePrice;
                                    }

                                    $showComparePrice = $displayPrice !== null
                                        && $lowestVariantPrice === null
                                        && $salePrice > 0
                                        && $basePrice > 0
                                        && $salePrice < $basePrice;

                                    $offerCap = $displayPrice ?? ($salePrice > 0 ? $salePrice : ($basePrice > 0 ? $basePrice : null));
                                    $showOfferPanel = $offerCap !== null
                                        && (
                                            ($errors->has('offer_price') && (string) old('product_id') === (string) $product->id)
                                            || (string) request('offer') === (string) $product->id
                                        );
                                }
                            @endphp

                            <article class="conversation-card h-full rounded-2xl border border-slate-200 bg-white shadow-sm">
                                <div class="flex min-h-[320px] flex-col p-4 sm:p-5">
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

                                    @if($product)
                                        <div class="mb-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                            <div class="flex gap-3">
                                                <a href="{{ $productUrl }}" class="shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-white">
                                                    @if($thumb)
                                                        <img src="{{ $thumb }}" alt="{{ $product->name }}" class="h-20 w-20 object-cover">
                                                    @else
                                                        <div class="flex h-20 w-20 items-center justify-center bg-slate-100 text-slate-500">
                                                            <i class="fa-solid fa-box-open"></i>
                                                        </div>
                                                    @endif
                                                </a>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex flex-wrap items-start gap-2">
                                                        <a href="{{ $productUrl }}" class="text-sm font-semibold text-slate-900 transition hover:text-emerald-700">
                                                            {{ \Illuminate\Support\Str::limit($product->name, 42) }}
                                                        </a>
                                                        <a href="{{ $productUrl }}" class="inline-flex items-center rounded-full bg-sky-100 px-2 py-0.5 text-[11px] font-semibold text-sky-700">
                                                            Open listing
                                                        </a>
                                                    </div>
                                                    <p class="mt-1 text-xs text-slate-500">
                                                        {{ \Illuminate\Support\Str::limit(trim(strip_tags((string) ($product->description ?? ''))) ?: 'No description available', 92) }}
                                                    </p>
                                                    <div class="mt-3 flex flex-wrap items-center gap-2">
                                                        @if($displayPrice !== null)
                                                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">
                                                                @if($priceLabel)
                                                                    <span class="mr-1 uppercase tracking-[0.1em] text-[10px] text-emerald-700">{{ $priceLabel }}</span>
                                                                @endif
                                                                {{ money($displayPrice) }}
                                                            </span>
                                                            @if($showComparePrice)
                                                                <span class="inline-flex items-center rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                                                    Was {{ money($basePrice) }}
                                                                </span>
                                                            @endif
                                                        @else
                                                            <span class="inline-flex items-center rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                                                Contact for price
                                                            </span>
                                                        @endif

                                                        @if($latestOffer)
                                                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                                                Your offer {{ $latestOffer->formatted_price }}
                                                            </span>
                                                            <span class="inline-flex items-center rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-700">
                                                                {{ $latestOffer->status_label }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                                @if($offerCap !== null)
                                                    <button type="button" class="inline-flex items-center justify-center rounded-xl border border-emerald-600 bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-500" data-offer-toggle="offer-panel-{{ $product->id }}">
                                                        <i class="fa-solid fa-tag mr-1"></i>{{ $latestOffer ? 'Update Offer' : 'Make Offer' }}
                                                    </button>
                                                @endif
                                                <a href="{{ $conversationUrl }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                                    <i class="fa-regular fa-comments mr-1"></i>Open Messages
                                                </a>
                                                @if($latestOffer)
                                                    <a href="{{ route('buyer.offers.details', $latestOffer->id) }}" class="inline-flex items-center justify-center rounded-xl border border-sky-600 px-3 py-1.5 text-xs font-semibold text-sky-700 transition hover:bg-sky-50">
                                                        <i class="fa-regular fa-file-lines mr-1"></i>View Offer
                                                    </a>
                                                @endif
                                            </div>

                                            @if($offerCap !== null)
                                                <div id="offer-panel-{{ $product->id }}" class="mt-3 rounded-2xl border border-slate-200 bg-white p-3 {{ $showOfferPanel ? '' : 'hidden' }}">
                                                    <form method="POST" action="{{ route('offers.store') }}" class="flex flex-col gap-3">
                                                        @csrf
                                                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                                                        <div>
                                                            <label for="offer-price-{{ $product->id }}" class="mb-1 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Offer Price</label>
                                                            <input type="number"
                                                                   name="offer_price"
                                                                   id="offer-price-{{ $product->id }}"
                                                                   min="1"
                                                                   max="{{ number_format($offerCap, 2, '.', '') }}"
                                                                   step="0.01"
                                                                   value="{{ $offerInputValue }}"
                                                                   placeholder="{{ number_format($offerCap, 2) }}"
                                                                   class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                                                                   required>
                                                            @if($errors->has('offer_price') && (string) old('product_id') === (string) $product->id)
                                                                <p class="mt-1 text-xs font-medium text-rose-600">{{ $errors->first('offer_price') }}</p>
                                                            @endif
                                                        </div>

                                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                                            <p class="text-xs text-slate-500">
                                                                Your offer cannot exceed {{ money($offerCap) }}.
                                                            </p>
                                                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-500">
                                                                <i class="fa-regular fa-paper-plane mr-1"></i>Send Offer
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="mb-2 flex items-center">
                                            <div class="mr-2 flex h-9 w-9 items-center justify-center rounded border bg-slate-100">
                                                <i class="fa-regular fa-comments text-slate-600"></i>
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
                                        @if(!$product)
                                            <a href="{{ $conversationUrl }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-500">
                                                <i class="fa-regular fa-comments mr-1"></i>Open
                                            </a>
                                        @endif
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-offer-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            const panelId = this.getAttribute('data-offer-toggle');
            const panel = panelId ? document.getElementById(panelId) : null;

            if (panel) {
                panel.classList.toggle('hidden');
            }
        });
    });
});
</script>
@endpush
@endsection
