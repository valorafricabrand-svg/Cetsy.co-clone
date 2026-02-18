@extends('theme.'.theme().'.layouts.app')
@section('title', 'Shop Favorites')

@section('main')
<section class="bg-slate-50 py-8 md:py-10">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
    <div class="grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      @include('seller.partials.sidebar')
      <div class="space-y-6">
<div class="content">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="h4 mb-1">Shop Favorites</h1>
            <p class="text-slate-500 mb-0">See which customers have added your products to their favorites</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-emerald-600 text-white border-emerald-600 fs-6 px-3 py-2">
                <i class="bi bi-heart-fill mr-1"></i>{{ $favorites->count() }} total favorites
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border px-4 py-3 text-sm border-emerald-200 bg-emerald-50 text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800">{{ session('warning') }}</div>
    @endif

    @if($favorites->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow border-0">
            <div class="p-4 text-center py-5">
                <div class="empty-state">
                    <i class="bi bi-heart text-slate-500" style="font-size: 4rem;"></i>
                    <h4 class="mt-3 text-slate-500">No shop favorites yet</h4>
                    <p class="text-slate-500 mb-0">When customers add your products to their favorites, they'll appear here.</p>
                </div>
            </div>
        </div>
    @else
        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-12 gap-3 mb-4">
            <div class="-span-3">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow border-0 bg-emerald-600 text-white border-emerald-600">
                    <div class="p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-box-seam fs-1"></i>
                            </div>
                            <div class="flex-grow-1 ml-3">
                                <h5 class="mb-1">{{ $favoritesByProduct->count() }}</h5>
                                <p class="mb-0 opacity-75">Products Favorited</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="-span-3">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow border-0 bg-emerald-100 text-emerald-800 border-emerald-200 text-white">
                    <div class="p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-people fs-1"></i>
                            </div>
                            <div class="flex-grow-1 ml-3">
                                <h5 class="mb-1">{{ $favorites->unique('user_id')->count() }}</h5>
                                <p class="mb-0 opacity-75">Unique Customers</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="-span-3">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow border-0 bg-sky-100 text-sky-800 border-sky-200 text-white">
                    <div class="p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-calendar-heart fs-1"></i>
                            </div>
                            <div class="flex-grow-1 ml-3">
                                <h5 class="mb-1">{{ $favorites->where('created_at', '>=', now()->subDays(7))->count() }}</h5>
                                <p class="mb-0 opacity-75">This Week</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="-span-3">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow border-0 bg-amber-100 text-amber-800 border-amber-200 text-slate-900">
                    <div class="p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-chat-dots fs-1"></i>
                            </div>
                            <div class="flex-grow-1 ml-3">
                                <h5 class="mb-1">{{ $favoritesMessagesTotal }}</h5>
                                <p class="mb-0">Messages from Favorites <span class="text-slate-500">(7d: {{ $favoritesMessagesWeek }})</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Favorites by Product --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow border-0">
            <div class="border-b border-slate-200 px-4 py-3 bg-white border-bottom">
                <div class="flex items-center">
                    <i class="bi bi-heart-fill mr-2 text-rose-600"></i>
                    <h5 class="mb-0">Favorites by Product</h5>
                </div>
            </div>
            <div class="p-4 p-0">
                @foreach($favoritesByProduct as $productId => $productFavorites)
                    @php
                        $product = $productFavorites->first()->product;
                        $favoriteCount = $productFavorites->count();
                        $uniqueBuyers = $productFavorites->unique('user_id')->count();
                        $productUrl = route('products.show', $product->slug ?? $product->id);
                    @endphp
                    <div class="product-favorites-section border-bottom">
                        <div class="p-4">
                            <div class="flex items-center mb-3">
                                <div class="product-info flex items-center flex-grow-1">
                                    @if($product->media->first())
                                        <a href="{{ $productUrl }}" class="product-thumb-link rounded mr-3" aria-label="Open {{ $product->name }}">
                                            <img src="{{ asset('storage/' . $product->media->first()->url) }}" 
                                                 alt="{{ $product->name }}" 
                                                 class="rounded" 
                                                 style="width:60px;height:60px;object-fit:cover;">
                                        </a>
                                    @else
                                        <a href="{{ $productUrl }}" class="product-thumb-link bg-slate-50 border rounded mr-3 flex items-center justify-center" aria-label="Open {{ $product->name }}" style="width:60px;height:60px;">
                                            <i class="bi bi-box text-secondary"></i>
                                        </a>
                                    @endif
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 font-bold">{{ $product->name }}</h6>
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-emerald-600 text-white border-emerald-600">{{ shop_currency() }} {{ number_format($product->price, 2) }}</span>
                                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-emerald-100 text-emerald-800 border-emerald-200">{{ $favoriteCount }} favorites</span>
                                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold bg-sky-100 text-sky-800 border-sky-200">{{ $uniqueBuyers }} unique customers</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="product-actions">
                                    <a href="{{ $productUrl }}" 
                                       class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition border border-emerald-600 text-emerald-700 hover:bg-emerald-50 px-2.5 py-1.5 text-xs rounded-lg">
                                        <i class="bi bi-eye mr-1"></i>View Product
                                    </a>
                                </div>
                            </div>

                            {{-- Buyers who favorited this product --}}
                            <div class="buyers-section">
                                <h6 class="text-slate-500 mb-3">
                                    <i class="bi bi-people mr-1"></i>Customers who favorited this product
                                </h6>
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-12 gap-3">
                                    @foreach($productFavorites->take(6) as $favorite)
                                        <div class="-span-6 -span-4">
                                            <div class="buyer-card border rounded p-3">
                                                <div class="flex items-center">
                                                    <div class="avatar-sm bg-emerald-600 text-white border-emerald-600 rounded-full flex items-center justify-center mr-3" 
                                                         style="width:40px;height:40px;">
                                                        {{ strtoupper(substr($favorite->user->name ?? 'U', 0, 1)) }}
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 font-semibold">{{ $favorite->user->name ?? 'Unknown Customer' }}</h6>
                                                        <div class="text-slate-500 text-xs">
                                                            <i class="bi bi-envelope mr-1"></i>
                                                            {{ \Illuminate\Support\Str::limit($favorite->user->email ?? '', 25) }}
                                                        </div>
                                                        <div class="text-slate-500 text-xs">
                                                            <i class="bi bi-calendar mr-1"></i>
                                                            {{ $favorite->created_at->diffForHumans() }}
                                                        </div>
                                                    </div>
                                                </div>
                                                @if(!empty($favorite->user))
                                                <div class="mt-2 flex flex-wrap gap-2">
                                                    <button
                                                        type="button"
                                                        class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#msg-{{ $product->id }}-{{ $favorite->user->id }}"
                                                        aria-expanded="false"
                                                        aria-controls="msg-{{ $product->id }}-{{ $favorite->user->id }}"
                                                    >
                                                        <i class="bi bi-chat-dots mr-1"></i> Message buyer
                                                    </button>
                                                    <a
                                                        href="{{ route('seller.messages.show', $product->id . '-' . $favorite->user->id) }}?prefill={{ urlencode('Hi '.($favorite->user->name ?? 'there').', thanks for favoriting \"'.$product->name.'\". Can I answer any questions or offer help?') }}"
                                                        class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100"
                                                    >
                                                        <i class="bi bi-chat-square-text mr-1"></i> View conversation
                                                    </a>
                                                    <div class="collapse mt-2" id="msg-{{ $product->id }}-{{ $favorite->user->id }}">
                                                        <form action="{{ route('messages.store') }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="receiver_id" value="{{ $favorite->user->id }}">
                                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                            <input type="hidden" name="source" value="favorites">
                                                            <div class="flex gap-2 mb-2">
                                                                <select id="msg-template-{{ $product->id }}-{{ $favorite->user->id }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" style="max-width:280px">
                                                                    <option value="">Select template…</option>
                                                                    <option>Hi {{ $favorite->user->name ?? 'there' }}, I saw you favorited "{{ $product->name }}" — we're open for offers. Would you like to make one?</option>
                                                                    <option>Hi {{ $favorite->user->name ?? 'there' }}, we can offer a special price on "{{ $product->name }}" for you. Interested?</option>
                                                                    <option>Hi {{ $favorite->user->name ?? 'there' }}, let me know if you have any questions about "{{ $product->name }}" or want to negotiate.</option>
                                                                </select>
                                                                <button type="button" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100" onclick="(function(){var s=document.getElementById('msg-template-{{ $product->id }}-{{ $favorite->user->id }}');var t=document.getElementById('msg-body-{{ $product->id }}-{{ $favorite->user->id }}'); if(s&&t&&s.value){ t.value=s.value; } })()">Apply</button>
                                                            </div>
                                                            <textarea id="msg-body-{{ $product->id }}-{{ $favorite->user->id }}" name="message" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" rows="2">Hi {{ $favorite->user->name ?? 'there' }}, I saw you favorited "{{ $product->name }}" — we're open for offers. Would you like to make one?</textarea>
                                                            <div class="flex justify-end mt-2">
                                                                <button type="submit" class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold transition px-2.5 py-1.5 text-xs rounded-lg border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">
                                                                    Send
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($productFavorites->count() > 6)
                                        <div class="col-span-12">
                                            <div class="text-center">
                                                <span class="text-slate-500">
                                                    +{{ $productFavorites->count() - 6 }} more customers
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Recent Favorites Timeline --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow border-0 mt-4">
            <div class="border-b border-slate-200 px-4 py-3 bg-white border-bottom">
                <div class="flex items-center">
                    <i class="bi bi-clock-history mr-2 text-primary"></i>
                    <h5 class="mb-0">Recent Favorites</h5>
                </div>
            </div>
            <div class="p-4 p-0">
                <div class="timeline-container">
                    @foreach($favorites->take(10) as $favorite)
                        <div class="timeline-item border-bottom p-3">
                            <div class="flex items-center">
                                <div class="timeline-icon bg-emerald-100 text-emerald-800 border-emerald-200 text-white rounded-full flex items-center justify-center mr-3" 
                                     style="width:40px;height:40px;">
                                    <i class="bi bi-heart-fill"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <strong>{{ $favorite->user->name ?? 'Unknown Customer' }}</strong>
                                            <span class="text-slate-500">favorited</span>
                                            <strong>{{ $favorite->product->name }}</strong>
                                        </div>
                                        <div class="text-slate-500 text-xs">
                                            {{ $favorite->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                    <div class="text-slate-500 text-xs mt-1">
                                        <i class="bi bi-envelope mr-1"></i>{{ $favorite->user->email ?? 'No email' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    .empty-state {
        padding: 2rem;
    }
    .product-favorites-section:last-child {
        border-bottom: none !important;
    }
    .buyer-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .buyer-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .product-thumb-link {
        display: inline-flex;
        text-decoration: none;
        transition: transform 0.2s, opacity 0.2s;
    }
    .product-thumb-link:hover {
        transform: translateY(-1px);
        opacity: 0.9;
    }
    .timeline-item {
        transition: background-color 0.2s;
    }
    .timeline-item:hover {
        background-color: #f8f9fa;
    }
    .timeline-icon {
        flex-shrink: 0;
    }
    .avatar-sm {
        font-weight: 600;
        font-size: 0.9rem;
    }
    @media (max-width: 768px) {
        .product-info {
            flex-direction: column;
            align-items: flex-start !important;
        }
        .product-actions {
            margin-top: 1rem;
        }
        .buyers-section .row {
            margin: 0;
        }
        .buyers-section .col-md-6 {
            padding: 0.5rem;
        }
    }
</style>
@endpush
      </div>
    </div>
  </div>
</section>
@endsection





