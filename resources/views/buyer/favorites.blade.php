@extends('theme.'.theme().'.layouts.app')

@section('header')
    <h2 class="text-2xl font-semibold text-slate-900">
        {{ __('Your Favorites') }}
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
            <h3 class="mb-1 text-2xl font-semibold text-slate-900">Favorites</h3>
            <p class="text-sm text-slate-500">Here are all the products you've added to your favorites.</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="p-4 sm:p-5">
                @if($favorites->isEmpty())
                    <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">You have no favorite products yet.</div>
                @else
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach($favorites as $product)
                            <article class="h-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                @php($thumb = product_thumb_url($product))
                                <img src="{{ $thumb }}" alt="{{ $product->name }}" class="aspect-[4/3] w-full object-cover">
                                <div class="p-4 text-center">
                                    <h6 class="line-clamp-2 text-base font-semibold text-slate-900">{{ $product->name }}</h6>
                                    <p class="mb-3 mt-1 text-sm text-slate-500">{{ get_currency() }} {{ number_format((float) ($product->price ?? 0), 2) }}</p>
                                    <a href="{{ route('products.show', $product->slug ?? $product->id) }}" class="inline-flex items-center justify-center rounded-xl border border-emerald-600 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50">View</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
            </div>
        </div>
    </div>
</div>
@endsection
