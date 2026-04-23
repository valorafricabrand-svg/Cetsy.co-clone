@extends('theme.'.theme().'.layouts.app')
@section('title', 'Offer Details')

@section('main')
@php
    $offerStatusClass = match($offer->status ?? null) {
        'accepted' => 'bg-emerald-100 text-emerald-700',
        'declined' => 'bg-rose-100 text-rose-700',
        'countered' => 'bg-indigo-100 text-indigo-700',
        default => 'bg-amber-100 text-amber-700',
    };
@endphp
<div class="py-8">
<div class="mx-auto w-full max-w-6xl px-4 sm:px-6">
    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12 lg:col-span-3">
            @include('buyer.partials.sidebar')
        </div>
        <div class="col-span-12 lg:col-span-9">
    <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="mb-0 text-base font-semibold">Offer Details</h2>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('buyer.offers') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                <i class="fa-solid fa-arrow-left mr-1"></i>Back to Offers
            </a>
            @if(($offer->product->shop->user_id ?? null))
                <a href="{{ route('buyer.messages.show', $offer->product_id . '-' . $offer->product->shop->user_id) }}" class="inline-flex items-center justify-center rounded-xl border border-emerald-600 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50">
                    <i class="fa-regular fa-comments mr-1"></i>Message Seller
                </a>
            @endif
        </div>
    </div>

    <div class="mb-4 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="p-4 sm:p-5">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 md:col-span-6">
                    <h6 class="mb-3">Product Information</h6>
                    <div class="mb-3 flex min-w-0 items-center">
                        @if($offer->product->media && $offer->product->media->count() > 0)
                            @php
                                $thumb = function_exists('product_thumb_url')
                                    ? product_thumb_url($offer->product)
                                    : (optional($offer->product->media->first())->url
                                        ? asset('storage/'.$offer->product->media->first()->url)
                                        : null);
                            @endphp
                            <img src="{{ $thumb }}"
                                 alt="{{ $offer->product->name }}"
                                 class="mr-3 h-20 w-20 shrink-0 rounded object-cover">
                        @else
                            <div class="mr-3 flex h-20 w-20 shrink-0 items-center justify-center rounded bg-slate-100">
                                <i class="fa-regular fa-image text-slate-500"></i>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <h6 class="mb-1 break-words">{{ $offer->product->name }}</h6>
                            <p class="mb-1 text-slate-500">{{ $offer->product->shop->name ?? 'Unknown Shop' }}</p>
                            <span class="inline-flex items-center rounded-full bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-700">{{ get_currency() }} {{ number_format($offer->product->price, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <h6 class="mb-3">Offer Details</h6>
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 sm:col-span-6">
                            <small class="text-slate-500">Your Offer</small>
                            <div class="font-bold text-emerald-600">{{ $offer->formatted_price }}</div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <small class="text-slate-500">Savings</small>
                            <div class="font-bold text-emerald-600">{{ get_currency() }} {{ number_format($offer->product->price - $offer->offer_price, 2) }}</div>
                        </div>
                    </div>
                    <div class="mt-2 grid grid-cols-12 gap-4">
                        <div class="col-span-12 sm:col-span-6">
                            <small class="text-slate-500">Status</small>
                            <div>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $offerStatusClass }}">{{ $offer->status_label }}</span>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <small class="text-slate-500">Date</small>
                            <div class="font-bold">{{ $offer->created_at->format('M d, Y H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if($offer->buyer_notes)
            <div class="mt-3">
                <h6 class="mb-2">Your Notes</h6>
                <div class="rounded bg-slate-100 p-3">{{ $offer->buyer_notes }}</div>
            </div>
            @endif

            @if($offer->seller_notes)
            <div class="mt-3">
                <h6 class="mb-2">Seller Notes</h6>
                <div class="rounded bg-slate-100 p-3">{{ $offer->seller_notes }}</div>
            </div>
            @endif

            @if($offer->counterOffers->count() > 0)
            <div class="mt-3">
                <h6 class="mb-2">Counter Offers</h6>
                @foreach($offer->counterOffers as $counterOffer)
                    @php
                        $counterStatusClass = match($counterOffer->status ?? null) {
                            'accepted' => 'bg-emerald-100 text-emerald-700',
                            'declined' => 'bg-rose-100 text-rose-700',
                            'countered' => 'bg-indigo-100 text-indigo-700',
                            default => 'bg-amber-100 text-amber-700',
                        };
                    @endphp
                    <div class="mb-2 rounded border p-3">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <strong class="text-sky-600">Counter Offer</strong>
                                <div class="font-bold">{{ $counterOffer->formatted_price }}</div>
                                @if($counterOffer->seller_notes)
                                    <div class="mt-1 text-xs text-slate-500">{{ $counterOffer->seller_notes }}</div>
                                @endif
                            </div>
                            <div class="text-left sm:text-right">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $counterStatusClass }}">{{ $counterOffer->status_label }}</span>
                                <div class="text-xs text-slate-500">{{ $counterOffer->created_at->format('M d, H:i') }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif

            @php
                $hasCounterOffers = $offer->counterOffers && $offer->counterOffers->count() > 0;
                $canRespond = $offer->status === 'pending' && $hasCounterOffers;
            @endphp

            <div class="mt-4">
                <h6 class="mb-2">Actions</h6>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ localized_route('listing.show', $offer->product->slug ?? $offer->product->id) }}" class="inline-flex items-center justify-center rounded-xl border border-emerald-600 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50">
                        <i class="fa-regular fa-eye mr-1"></i>View Product
                    </a>

                    @if($canRespond)
                    <button type="button" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-500" onclick="toggleRespondSection()">
                        <i class="fa-regular fa-circle-check mr-1"></i>Respond to Counter Offer
                    </button>
                    @endif

                    @if(($offer->status === 'accepted') && $offer->order)
                        <a href="{{ route('pay_now', $offer->order->id) }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-500">
                            <i class="fa-regular fa-credit-card mr-1"></i>Pay Now
                        </a>
                        <a href="{{ route('buyer.orders.show', $offer->order->id) }}" class="inline-flex items-center justify-center rounded-xl border border-sky-600 px-3 py-1.5 text-xs font-semibold text-sky-700 transition hover:bg-sky-50">
                            <i class="fa-solid fa-receipt mr-1"></i>View Order
                        </a>
                    @endif
                </div>
            </div>

            @if($canRespond)
            <div id="respondSection" class="mt-3 hidden">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-4 sm:p-5">
                        <form method="POST" action="{{ route('buyer.offers.respond', ['offerId' => $offer->id]) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="mb-1 block text-sm font-medium text-slate-700">Your Response</label>
                                <select name="response" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500" required onchange="document.getElementById('counterPriceWrap').style.display = (this.value==='counter') ? 'block' : 'none';">
                                    <option value="">Choose response...</option>
                                    <option value="accept">Accept Counter Offer</option>
                                    <option value="decline">Decline Counter Offer</option>
                                    <option value="counter">Make New Counter Offer</option>
                                </select>
                            </div>
                            <div class="mb-3" id="counterPriceWrap" style="display:none;">
                                <label class="mb-1 block text-sm font-medium text-slate-700">Your Counter Offer Price</label>
                                <input type="number" name="counter_price" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" step="0.01" min="0">
                            </div>
                            <div class="mb-3">
                                <label class="mb-1 block text-sm font-medium text-slate-700">Message (Optional)</label>
                                <textarea name="message" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500" rows="3" placeholder="Add a message to your response..."></textarea>
                            </div>
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500">Submit Response</button>
                        </form>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
        </div>
    </div>
</div>
</div>
@if(($offer->status === 'pending') && ($offer->counterOffers && $offer->counterOffers->count() > 0))
@push('scripts')
<script>
function toggleRespondSection() {
    const section = document.getElementById('respondSection');
    if (!section) return;
    section.classList.toggle('hidden');
}
</script>
@endpush
@endif
@endsection
