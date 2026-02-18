@extends('theme.'.theme().'.layouts.app')

@section('header')
    <h2 class="font-semibold fs-3 text-slate-900">
        {{ __('Your Favorites') }}
    </h2>
@endsection

@section('main')
<div class="content">
    <div class="container-xxl">
        <div class="mb-4">
            <h3 class="text-slate-900 mb-1">Favorites</h3>
            <p class="text-slate-500">
                Here are all the products you've added to your favorites.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0">
            <div class="p-4 sm:p-5">
                @if($favorites->isEmpty())
                    <div class="rounded-xl border px-4 py-3 text-sm border-sky-200 bg-sky-50 text-sky-800 mb-0">You have no favorite products yet.</div>
                @else
                    <div class="grid grid-cols-12 gap-4 gap-3">
                        @foreach($favorites as $product)
                            <div class="md:col-span-3">
                                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm border-0 h-full">
                                    @php($thumb = product_thumb_url($product))
                                    <img src="{{ $thumb }}" class="card-img-top" alt="{{ $product->name }}">
                                    <div class="p-4 sm:p-5 text-center">
                                        <h6 class="text-lg font-semibold text-slate-900">{{ $product->name }}</h6>
                                        <p class="text-slate-500 mb-1">{{ get_currency() }} {{ number_format($product->price, 2) }}</p>
                                        <a href="{{ route('products.show', $product->slug ?? $product->id) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition px-3 py-1.5 text-xs border border-emerald-600 text-emerald-700 hover:bg-emerald-50">View</a>
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
@endsection 




