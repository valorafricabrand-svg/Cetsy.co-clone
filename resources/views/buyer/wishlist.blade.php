{{-- resources/views/wishlist/index.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('header')
    <h2 class="text-2xl font-semibold text-slate-900">
        {{ __('My Wishlist') }}
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
        <div class="mb-4">
            <h4 class="mb-1 text-2xl font-semibold text-slate-900">Wishlist</h4>
            <p class="mb-0 text-sm text-slate-500">Here are the items you've added to your wishlist.</p>
        </div>

        @if ($wishlistItems->isEmpty())
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Your wishlist is empty.
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($wishlistItems as $item)
                    @php
                        $product = $item->product;
                        $firstImg = $product->media->first();
                        $imgUrl = $firstImg
                            ? asset('storage/' . ($firstImg->url ?? $firstImg->file_path))
                            : asset('assets/img/placeholder.svg');
                    @endphp

                    <article class="h-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <a href="{{ route('products.show', $product->slug ?? $product->id) }}" class="block aspect-[4/3] overflow-hidden">
                            <img src="{{ $imgUrl }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-300 hover:scale-105">
                        </a>

                        <div class="p-4">
                            <h5 class="mb-1 text-base font-semibold text-slate-900">
                                <a href="{{ route('products.show', $product->slug ?? $product->id) }}" class="no-underline text-slate-900 hover:text-emerald-700">
                                    {{ $product->name }}
                                </a>
                            </h5>
                            <p class="mb-0 text-sm font-semibold text-slate-600">
                                {{ get_currency() }} {{ number_format((float) ($product->price ?? 0), 2) }}
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 px-4 py-3">
                            <a href="{{ route('listing.show', $product->slug ?? $product->id) }}" class="inline-flex items-center justify-center rounded-xl border border-emerald-600 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50">
                                View
                            </a>

                            <form method="POST" action="{{ route('wishlist.remove', $item->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-rose-600 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-50">
                                    <i class="fas fa-trash-alt mr-1"></i>Remove
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
            </div>
        </div>
    </div>
</div>
@endsection
