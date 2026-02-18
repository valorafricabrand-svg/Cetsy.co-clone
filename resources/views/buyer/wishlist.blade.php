{{-- resources/views/wishlist/index.blade.php --}}
@extends('theme.'.theme().'.layouts.app')

@section('header')
    <h2 class="font-semibold fs-3 text-slate-900">
        {{ __('My Wishlist') }}
    </h2>
@endsection

@section('main')
<div class="content py-5">
    <div class="container-xxl">

        <div class="mb-4">
            <h4 class="text-slate-900 mb-1">Wishlist</h4>
            <p class="text-slate-500 mb-0">Here are the items youâ€™ve added to your wishlist.</p>
        </div>

        @if ($wishlistItems->isEmpty())
            <div class="rounded-xl border px-4 py-3 text-sm border-amber-200 bg-amber-50 text-amber-800">
                Your wishlist is empty.
            </div>
        @else
            <div class="grid grid-cols-12 gap-4 row-cols-1 row-cols-md-2 row-cols-lg-3">
                @foreach ($wishlistItems as $item)
                    @php
                        $product = $item->product;
                        $firstImg = $product->media->first();
                        $imgUrl   = $firstImg
                                    ? asset('storage/' . ($firstImg->url   ?? $firstImg->file_path))
                                    : asset('assets/img/placeholder.svg');
                    @endphp

                    <div class="col-span-12">
                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm h-full border-0">

                            {{-- Product cover --}}
                            <a href="{{ route('products.show', $product->slug ?? $product->id) }}"
                               class="ratio ratio-4x3">
                                <img src="{{ $imgUrl }}"
                                     alt="{{ $product->name }}"
                                     class="object-fit-cover rounded-top">
                            </a>

                            <div class="p-4 sm:p-5">
                                <h5 class="text-lg font-semibold text-slate-900 text-truncate mb-1">
                                    <a href="{{ route('products.show', $product->slug ?? $product->id) }}"
                                       class="text-slate-900 no-underline">
                                        {{ $product->name }}
                                    </a>
                                </h5>
                                <p class="text-slate-500 font-semibold mb-0">
                                    {{ money() }}
                                </p>
                            </div>

                            <div class="border-t border-slate-200 px-4 py-3 bg-white border-0 flex justify-between">
                                <a href="{{ route('listing.show', $product ?? $product->id) }}"
                                   class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-emerald-600 text-emerald-700 hover:bg-emerald-50">
                                    View
                                </a>

                                <form method="POST" action="{{ route('wishlist.remove', $item->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-rose-600 text-rose-700 hover:bg-rose-50">
                                        <i class="fas fa-trash-alt"></i>
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</div>
@endsection






