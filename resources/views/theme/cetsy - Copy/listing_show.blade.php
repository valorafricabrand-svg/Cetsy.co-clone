{{-- resources/views/items/show.blade.php --}}
@extends('layouts.frontapp')

@section('title', $product->name . ' – Item Details')

@section('main')
<div class="container py-5"
     x-data="{
        qty: 1,
        busy: false,
        shippingProfileId: {{ $product->shippingProfiles->firstWhere('is_default', true)->id ?? 'null' }},
        share() {
            navigator.clipboard.writeText('{{ url()->current() }}')
               .then(() => alert('Link copied to clipboard!'))
               .catch(()  => alert('Unable to copy link.'));
        }
     }"
>
    {{-- Flash --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {!! session('success') !!}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-5">
        {{-- ────────── Image Carousel ────────── --}}
        <div class="col-lg-6">
            <div id="productCarousel" class="carousel slide shadow-sm rounded mb-4" data-bs-ride="carousel">
                <div class="carousel-inner rounded">
                    @foreach($product->media as $i => $media)
                        <div class="carousel-item @if($i===0) active @endif">
                            <img src="{{ asset('storage/'.$media->url) }}"
                                 class="d-block w-100 rounded"
                                 alt="{{ $product->name }}"
                                 style="object-fit:cover;max-height:500px;">
                        </div>
                    @endforeach
                </div>

                @if($product->media->count() > 1)
                    <button class="carousel-control-prev" type="button"
                            data-bs-target="#productCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button"
                            data-bs-target="#productCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                @endif
            </div>

            @if($product->media->count() > 1)
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    @foreach($product->media as $i => $media)
                        <img src="{{ asset('storage/'.$media->url) }}"
                             style="width:75px;height:75px;object-fit:cover;cursor:pointer;"
                             class="img-thumbnail @if($i===0) border-success @endif"
                             data-bs-target="#productCarousel"
                             data-bs-slide-to="{{ $i }}">
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ────────── Details & Actions ────────── --}}
        <div class="col-lg-6">
            <h1 class="display-5 fw-bold mb-2">{{ $product->name }}</h1>
            <p class="h4 text-success fw-semibold mb-3">KES {{ number_format($product->price,2) }}</p>

            {{-- Quick-action bar --}}
            <div class="d-flex flex-wrap gap-2 mb-4">
                {{-- Favourite --}}
                <form method="POST" action="{{ route('favorites.toggle') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button class="btn btn-outline-secondary d-flex align-items-center gap-1">
                        <i class="fas fa-heart"></i><span class="d-none d-md-inline">Favourite</span>
                    </button>
                </form>

                {{-- Share --}}
                <button class="btn btn-outline-secondary d-flex align-items-center gap-1" @click="share">
                    <i class="fas fa-share"></i><span class="d-none d-md-inline">Share</span>
                </button>

                {{-- Make Offer --}}
                <button class="btn btn-outline-primary d-flex align-items-center gap-1"
                        data-bs-toggle="modal" data-bs-target="#offerModal">
                    <i class="fas fa-hand-holding-usd"></i><span class="d-none d-md-inline">Make an Offer</span>
                </button>

                {{-- Message Seller --}}
                <button class="btn btn-outline-primary d-flex align-items-center gap-1"
                        data-bs-toggle="modal" data-bs-target="#messageModal">
                    <i class="fas fa-comment-dots"></i><span class="d-none d-md-inline">Message Seller</span>
                </button>
            </div>

            {{-- Description --}}
            <div class="mb-4 text-secondary fs-6">{!! $product->description !!}</div>

            {{-- Meta --}}
            <p class="mb-2">
                <strong>Category:</strong>
                @if($product->category)
                    <a href="{{ route('category.show',$product->category->slug) }}"
                       class="badge bg-success text-decoration-none">{{ $product->category->name }}</a>
                @else
                    <span class="badge bg-secondary">Uncategorized</span>
                @endif
            </p>
            <p class="mb-4">
                <strong>Shop:</strong>
                <a href="{{ route('shop.show',$product->shop) }}" class="text-success text-decoration-none">
                    {{ $product->shop->name }}
                </a>
            </p>

            {{-- Purchase block --}}
            <div class="border rounded p-4 bg-light">
                {{-- qty & shipping selectors (shared) --}}
                <div class="mb-3 d-flex align-items-center gap-2">
                    <span class="fw-semibold">Qty:</span>
                    <button type="button" class="btn btn-outline-secondary"
                            @click="qty=Math.max(1,qty-1)" :disabled="qty<=1">−</button>
                    <input type="text" class="form-control text-center" style="width:70px;" :value="qty" readonly>
                    <button type="button" class="btn btn-outline-secondary" @click="qty++">+</button>
                </div>

                @if($product->shippingProfiles->count())
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Shipping Profile:</label>
                        <select x-model="shippingProfileId" class="form-select">
                            @foreach($product->shippingProfiles as $profile)
                                <option value="{{ $profile->id }}">
                                    {{ $profile->name }} – KES {{ number_format($profile->base_rate,2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Buttons row --}}
                <div class="d-flex gap-2">
                    {{-- Add to Cart --}}
                    <form method="POST" action="{{ route('cart.add') }}" class="flex-fill"
                          @submit.prevent="busy=true;$el.submit()">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity"  :value="qty">
                        <input type="hidden" name="shipping_profile_id" :value="shippingProfileId">
                        <button class="btn btn-success btn-lg w-100" :disabled="busy">
                            <i class="fas fa-cart-plus me-2"></i>Add to Cart
                        </button>
                    </form>

                    {{-- Buy It Now --}}
                    <form method="POST" action="{{ route('cart.buy') }}" class="flex-fill"
                          @submit.prevent="busy=true;$el.submit()">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity"  :value="qty">
                        <input type="hidden" name="shipping_profile_id" :value="shippingProfileId">
                        <button class="btn btn-primary btn-lg w-100" :disabled="busy">
                            <i class="fas fa-bolt me-2"></i>Buy&nbsp;It&nbsp;Now ✅
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ────────── Offer & Message modals (unchanged) ────────── --}}
<div class="modal fade" id="messageModal" tabindex="-1"
     aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('messages.store') }}">
            @csrf
            {{-- Receiver (shop owner) --}}
            <input type="hidden" name="receiver_id" value="{{ $product->shop->user_id }}">
            {{-- Optional product context --}}
            <input type="hidden" name="product_id" value="{{ $product->id }}">

            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel">
                    Message&nbsp;Seller – {{ $product->shop->name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <label for="messageBody" class="form-label">
                    Your message
                </label>
                <textarea id="messageBody"
                          name="message"
                          rows="4"
                          class="form-control"
                          placeholder="Hi, I’d like to ask about…"
                          required></textarea>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    Send&nbsp;Message
                </button>
                <button type="button" class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>


{{-- resources/views/items/partials/offer-modal.blade.php --}}
<div class="modal fade" id="offerModal" tabindex="-1" aria-labelledby="offerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('offers.store') }}">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">

            <div class="modal-header">
                <h5 class="modal-title" id="offerModalLabel">Make an Offer for {{ $product->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <label for="offerPrice" class="form-label">Your offer price (KES)</label>
                <input
                    type="number"
                    name="offer_price"
                    id="offerPrice"
                    min="1"
                    step="1"
                    class="form-control"
                    placeholder="Enter a price"
                    required
                >
                <small class="text-muted d-block mt-2">
                    The seller will be notified and can accept or counter your offer.
                </small>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    Submit Offer
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
@endsection




