{{-- resources/views/wishlist/index.blade.php --}}
@extends('layouts.app')

@section('header')
    <h2 class="fw-semibold fs-3 text-dark">
        {{ __('My Wishlist') }}
    </h2>
@endsection

@section('content')
<div class="content py-5">
    <div class="container-xxl">

        <div class="mb-4">
            <h4 class="text-dark mb-1">Wishlist</h4>
            <p class="text-muted mb-0">Here are the items you’ve added to your wishlist.</p>
        </div>

        @if ($wishlistItems->isEmpty())
            <div class="alert alert-warning">
                Your wishlist is empty.
            </div>
        @else
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                @foreach ($wishlistItems as $item)
                    @php
                        $product = $item->product;
                        $firstImg = $product->media->first();
                        $imgUrl   = $firstImg
                                    ? asset('storage/' . ($firstImg->url   ?? $firstImg->file_path))
                                    : asset('assets/img/placeholder.svg');
                    @endphp

                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm">

                            {{-- Product cover --}}
                            <a href="{{ route('products.show', $product->slug ?? $product->id) }}"
                               class="ratio ratio-4x3">
                                <img src="{{ $imgUrl }}"
                                     alt="{{ $product->name }}"
                                     class="object-fit-cover rounded-top">
                            </a>

                            <div class="card-body">
                                <h5 class="card-title text-truncate mb-1">
                                    <a href="{{ route('products.show', $product->slug ?? $product->id) }}"
                                       class="text-dark text-decoration-none">
                                        {{ $product->name }}
                                    </a>
                                </h5>
                                <p class="text-muted fw-semibold mb-0">
                                    {{ get_currency() }} {{ number_format($product->price, 2) }}
                                </p>
                            </div>

                            <div class="card-footer bg-white border-0 d-flex justify-content-between">
                                <a href="{{ route('listing.show', $product ?? $product->id) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    View
                                </a>

                                <form method="POST" action="{{ route('wishlist.remove', $item->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
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
